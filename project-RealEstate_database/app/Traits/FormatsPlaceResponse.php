<?php

namespace App\Traits;

use App\Models\Places;
use App\Traits\FormatsCityResponse;

trait FormatsPlaceResponse
{
    use FormatsCityResponse;

    protected function formatPlace(Places $place): array
    {
        $data = $place->toArray();

        if ($place->relationLoaded('city') && $place->city) {
            $data['city'] = $this->formatCity($place->city);
        }

        return $data;
    }
}
