<?php

namespace App\Http\Requests\Admin;

// =============================================================================
// Form Request (Admin): إضافة حي/منطقة ضمن مدينة
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;

class StorePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** rules: cities_id موجود + اسم المنطقة. */
    public function rules(): array
    {
        return [
            'cities_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
