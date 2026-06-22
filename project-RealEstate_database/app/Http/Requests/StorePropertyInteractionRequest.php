<?php

namespace App\Http\Requests;

use App\Enums\InteractionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'interaction_type' => ['required', Rule::enum(InteractionType::class)],
            'interaction_score' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];

        if (! $this->route('estate')) {
            $rules['estate_id'] = ['required', 'integer', Rule::exists('estates', 'id')];
        }

        return $rules;
    }
}
