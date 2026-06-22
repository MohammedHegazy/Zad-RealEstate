<?php

namespace App\Traits;

use App\Models\Cities;
use App\Services\FileUploadService;

trait FormatsCityResponse
{
    protected function formatCity(Cities $city, ?FileUploadService $uploader = null): array
    {
        $uploader ??= app(FileUploadService::class);

        $data = $city->toArray();
        $data['image_url'] = $uploader->publicUrl($city->image);

        if ($city->relationLoaded('places')) {
            $data['places'] = $city->places->map(fn ($place) => $place->only(['id', 'name', 'cities_id']))->values()->all();
        }

        return $data;
    }
}
