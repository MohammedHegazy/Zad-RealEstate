<?php

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;

class EstateMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'north' => ['sometimes', 'numeric', 'between:-90,90', 'required_with:south,east,west'],
            'south' => ['sometimes', 'numeric', 'between:-90,90', 'lte:north'],
            'east' => ['sometimes', 'numeric', 'between:-180,180'],
            'west' => ['sometimes', 'numeric', 'between:-180,180'],
        ];
    }
}
