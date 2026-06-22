<?php

namespace App\Http\Requests;

use App\Enums\InvestmentGoal;
use App\Enums\PropertyFunction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('preferred_city_id') && ! $this->filled('cities_id')) {
            $this->merge(['cities_id' => $this->input('preferred_city_id')]);
        }
    }

    public function rules(): array
    {
        $cityId = $this->input('cities_id') ?? $this->input('preferred_city_id');

        return [
            'preferred_city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'cities_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'places_id' => [
                'nullable',
                'integer',
                Rule::exists('places', 'id')->when(
                    $cityId,
                    fn ($rule) => $rule->where('cities_id', (int) $cityId)
                ),
            ],
            'min_budget' => ['nullable', 'numeric', 'min:0'],
            'max_budget' => ['nullable', 'numeric', 'min:0', 'gte:min_budget'],
            'preferred_property_type' => ['nullable', 'string', 'max:255'],
            'preferred_rooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'property_function' => ['nullable', Rule::enum(PropertyFunction::class)],
            'investment_goal' => ['nullable', Rule::enum(InvestmentGoal::class)],
            'risk_level' => ['nullable', 'string', Rule::in(config('realestate.portfolio_risk_levels', ['low', 'moderate', 'high']))],
            'interests' => ['nullable', 'array', 'max:20'],
            'interests.*' => ['string', 'max:100'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        unset($data['preferred_city_id']);

        return $data;
    }
}
