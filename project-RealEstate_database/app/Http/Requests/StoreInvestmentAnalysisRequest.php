<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvestmentAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'property_price' => ['required', 'numeric', 'min:0'],
            'monthly_rent' => ['nullable', 'numeric', 'min:0'],
            'annual_expenses' => ['nullable', 'numeric', 'min:0'],
            'maintenance_cost' => ['nullable', 'numeric', 'min:0'],
            'tax_cost' => ['nullable', 'numeric', 'min:0'],
            'occupancy_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        if (! $this->route('estate')) {
            $rules['estate_id'] = ['required', 'integer', Rule::exists('estates', 'id')];
        } else {
            $rules['property_price'] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }
}
