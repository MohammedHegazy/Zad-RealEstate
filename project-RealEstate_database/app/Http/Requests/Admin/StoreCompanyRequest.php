<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\WorkDaysValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    use WorkDaysValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeWorkDaysInput();
    }

    public function rules(): array
    {
        return array_merge([
            'user_id' => ['required', 'exists:users,id', Rule::unique('companies', 'user_id')],
            'places_id' => ['required', 'exists:places,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'employees_num' => ['nullable', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'status' => ['sometimes', 'string', Rule::in(config('realestate.company_statuses'))],
            'profile_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
            'banner_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
        ], $this->workDaysRules());
    }
}
