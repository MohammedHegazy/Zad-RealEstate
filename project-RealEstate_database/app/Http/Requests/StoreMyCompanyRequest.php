<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\WorkDaysValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreMyCompanyRequest extends FormRequest
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

    /** rules: بيانات الشركة + مكان + صور اختيارية. */
    public function rules(): array
    {
        return array_merge([
            'places_id' => ['required', 'exists:places,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'employees_num' => ['nullable', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'profile_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
            'banner_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
        ], $this->workDaysRules());
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()->company()->exists()) {
                $validator->errors()->add('company', 'You already have a company profile.');
            }
        });
    }
}
