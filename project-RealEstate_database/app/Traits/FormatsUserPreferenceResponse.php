<?php

namespace App\Traits;

use App\Models\UserPreference;

trait FormatsUserPreferenceResponse
{
    protected function formatUserPreference(UserPreference $preference): array
    {
        $data = [
            'id' => $preference->id,
            'user_id' => $preference->user_id,
            'preferred_city_id' => $preference->cities_id,
            'cities_id' => $preference->cities_id,
            'places_id' => $preference->places_id,
            'min_budget' => $preference->min_budget,
            'max_budget' => $preference->max_budget,
            'preferred_property_type' => $preference->preferred_property_type,
            'preferred_rooms' => $preference->preferred_rooms,
            'property_function' => $preference->property_function?->value,
            'investment_goal' => $preference->investment_goal?->value,
            'risk_level' => $preference->risk_level,
            'interests' => $preference->interests,
            'created_at' => $preference->created_at?->toIso8601String(),
            'updated_at' => $preference->updated_at?->toIso8601String(),
        ];

        if ($preference->relationLoaded('city') && $preference->city) {
            $data['city'] = $preference->city->only(['id', 'name']);
        }

        if ($preference->relationLoaded('place') && $preference->place) {
            $data['place'] = $preference->place->only(['id', 'name', 'cities_id']);
        }

        return $data;
    }
}
