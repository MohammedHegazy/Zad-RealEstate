<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\WorkDaysValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMyCompanyRequest extends FormRequest
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
            'places_id' => ['sometimes', 'exists:places,id'],
            'company_name' => ['sometimes', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'employees_num' => ['nullable', 'integer', 'min:1'],
            'description' => ['sometimes', 'string'],
            'profile_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
            'banner_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
        ], $this->workDaysRules(required: false));
    }
}
