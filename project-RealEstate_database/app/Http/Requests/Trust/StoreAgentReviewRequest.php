<?php

namespace App\Http\Requests\Trust;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
