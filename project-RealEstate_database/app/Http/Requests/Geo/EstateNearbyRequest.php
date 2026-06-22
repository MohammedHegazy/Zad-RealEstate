<?php

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;

class EstateNearbyRequest extends FormRequest
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
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'radius_km' => ['sometimes', 'numeric', 'min:0.1', 'max:'.$maxRadius],
        ];
    }
}
