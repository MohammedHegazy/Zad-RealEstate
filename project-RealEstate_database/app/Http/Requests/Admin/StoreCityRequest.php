<?php

namespace App\Http\Requests\Admin;

// =============================================================================
// Form Request (Admin): إضافة مدينة جديدة
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** rules: اسم فريد في cities + صورة اختيارية. */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:cities,name'],
            'image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
