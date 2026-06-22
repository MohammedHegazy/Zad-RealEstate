<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFavoriteAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_id' => [
                'required',
                'integer',
                Rule::exists('agents', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.exists' => 'The selected agent does not exist.',
        ];
    }
}
