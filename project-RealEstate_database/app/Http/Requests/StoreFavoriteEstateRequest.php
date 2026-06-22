<?php

namespace App\Http\Requests;

// =============================================================================
// Form Request: إضافة عقار إلى المفضلة
// =============================================================================
// exists مع where('status','active') — لا يُفضَّل إلا العقارات النشطة.

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFavoriteEstateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // estate_id: موجود و status = active
            'estate_id' => [
                'required',
                'integer',
                Rule::exists('estates', 'id')->where('status', 'active'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'estate_id.exists' => 'The selected estate does not exist or is not available.',
        ];
    }
}
