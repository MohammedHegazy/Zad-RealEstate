<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'companies_id' => ['sometimes', 'integer', 'exists:companies,id'],
            'trust_score' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'profile_image' => ['nullable', 'image', 'max:'.config('realestate.upload.max_image_kb')],
        ];
    }
}
