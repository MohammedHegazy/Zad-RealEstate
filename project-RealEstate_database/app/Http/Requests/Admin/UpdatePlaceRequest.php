<?php

namespace App\Http\Requests\Admin;

// =============================================================================
// Form Request (Admin): تحديث منطقة/حي
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cities_id' => ['sometimes', 'exists:cities,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
