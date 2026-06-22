<?php

namespace App\Http\Requests\Trust;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'review' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
