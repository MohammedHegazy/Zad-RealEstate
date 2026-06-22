<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: رفع صورة إعلان/بانر لعقار
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;

class StoreEstateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** rules: صورة مطلوبة؛ is_main لتحديد الإعلان الرئيسي. */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:'.config('realestate.upload.max_image_kb')],
            'is_main' => ['nullable', 'boolean'],
        ];
    }
}
