<?php

namespace App\Http\Requests\Trust;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVerificationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', Rule::in(config('realestate.verification_document_types'))],
            'document' => ['required_without:document_path', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_path' => ['required_without:document', 'string', 'max:500'],
        ];
    }
}
