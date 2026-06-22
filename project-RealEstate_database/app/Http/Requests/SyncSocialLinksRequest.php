<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncSocialLinksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'links' => ['sometimes', 'array'],
            'links.*.platform' => ['required_with:links', 'string', Rule::in(config('realestate.social_platforms'))],
            'links.*.url' => ['required_with:links', 'url', 'max:500'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'instagram' => ['nullable', 'string', 'max:500'],
        ];
    }
}
