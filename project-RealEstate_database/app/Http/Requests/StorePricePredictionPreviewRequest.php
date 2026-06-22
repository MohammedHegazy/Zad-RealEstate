<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePricePredictionPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estate_id' => ['nullable', 'integer', Rule::exists('estates', 'id')],
            'places_id' => ['nullable', 'integer', Rule::exists('places', 'id')],
            'place' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'space_of_estate' => ['required', 'numeric', 'min:0'],
            'is_furnished' => ['nullable', 'boolean'],
            'floor' => ['nullable', 'numeric', 'min:0'],
            'num_of_bedrooms' => ['nullable', 'numeric', 'min:0'],
            'num_of_livingrooms' => ['nullable', 'numeric', 'min:0'],
            'num_of_receptions' => ['nullable', 'numeric', 'min:0'],
            'num_of_bathrooms' => ['nullable', 'numeric', 'min:0'],
            'num_of_kitchens' => ['nullable', 'numeric', 'min:0'],
            'num_of_balconies' => ['nullable', 'numeric', 'min:0'],
            'date_of_build' => ['nullable', 'string', 'max:50'],
        ];
    }
}
