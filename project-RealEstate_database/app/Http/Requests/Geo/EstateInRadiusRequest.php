<?php

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;

class EstateInRadiusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxRadius = (float) config('realestate.geo.max_radius_km', 100);

        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['required', 'numeric', 'min:0.1', 'max:'.$maxRadius],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type_text' => ['sometimes', 'string', 'max:255'],
            'kind_text' => ['sometimes', 'string', 'max:255'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
