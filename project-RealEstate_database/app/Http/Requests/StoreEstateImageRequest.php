<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: رفع صورة معرض لعقار
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;

class StoreEstateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** rules: صورة + is_primary اختياري لصورة الغلاف. */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:'.config('realestate.upload.max_image_kb')],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
