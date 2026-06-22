<?php

namespace App\Traits;

use App\Http\Controllers\Concerns\ManagesEstateAds;
use App\Http\Controllers\Concerns\ManagesEstateImages;
use App\Http\Controllers\Concerns\ManagesEstateVideos;
use App\Models\Estate;
use App\Services\FileUploadService;
use App\Services\SocialLinkService;

trait FormatsEstateResponse
{
    use ManagesEstateAds, ManagesEstateImages, ManagesEstateVideos;
    protected function formatEstate(Estate $estate, ?FileUploadService $uploader = null): array
    {
        $uploader ??= app(FileUploadService::class);
        $socialLinks = app(SocialLinkService::class);

        $estate->loadMissing(['images', 'videos', 'ads']);

        $data = $estate->toArray();
        unset($data['images'], $data['videos'], $data['ads']);

        $data['social_links'] = $estate->relationLoaded('socialLinks')
            ? $socialLinks->formatCollection($estate)
            : [];

        $data['images'] = $estate->images
            ->map(fn ($image) => $this->formatEstateImage($image))
            ->values()
            ->all();

        $data['videos'] = $estate->videos
            ->map(fn ($video) => $this->formatEstateVideo($video, $uploader))
            ->values()
            ->all();

        $data['ads'] = $estate->ads
            ->map(fn ($ad) => $this->formatEstateAd($ad, $uploader))
            ->values()
            ->all();

        return $data;
    }

    protected function formatMapMarker(Estate $estate): array
    {
        return [
            'id' => $estate->id,
            'name' => $estate->name,
            'price' => $estate->price,
            'latitude' => $estate->latitude,
            'longitude' => $estate->longitude,
            'type_text' => $estate->type_text,
            'kind_text' => $estate->kind_text,
            'place' => $estate->relationLoaded('place') && $estate->place
                ? ['id' => $estate->place->id, 'name' => $estate->place->name]
                : null,
        ];
    }
}
