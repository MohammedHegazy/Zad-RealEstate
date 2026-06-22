<?php

namespace App\Http\Requests\Admin;

// =============================================================================
// Form Request (Admin): إنشاء وكيل مرتبط بمستخدم وشركة
// =============================================================================

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('agents', 'user_id'),
            ],
            'companies_id' => ['required', 'integer', 'exists:companies,id'],
            'profile_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
        ];
    }
}
