<?php

namespace App\Traits;

use App\Models\Estate;
use App\Models\Recommendation;
use App\Services\FileUploadService;

trait FormatsRecommendationResponse
{
    protected function formatRecommendation(Recommendation $recommendation): array
    {
        $data = [
            'id' => $recommendation->id,
            'user_id' => $recommendation->user_id,
            'estate_id' => $recommendation->estate_id,
            'recommendation_score' => $recommendation->recommendation_score,
            'matching_percentage' => $recommendation->matching_percentage,
            'score_factors' => $recommendation->score_factors,
            'why_recommended' => $recommendation->recommendation_reason,
            'recommendation_reason' => $recommendation->recommendation_reason,
            'is_active' => $recommendation->is_active,
            'created_at' => $recommendation->created_at?->toIso8601String(),
            'updated_at' => $recommendation->updated_at?->toIso8601String(),
        ];

        if ($recommendation->relationLoaded('estate') && $recommendation->estate) {
            $data['estate'] = $this->formatRecommendationEstate($recommendation->estate);
        }

        return $data;
    }

    /**
     * Lightweight estate payload for recommendation responses.
     *
     * @return array<string, mixed>
     */
    protected function formatRecommendationEstate(Estate $estate): array
    {
        $data = $estate->only([
            'id',
            'name',
            'price',
            'monthly_rent',
            'roi',
            'expected_annual_income',
            'status',
            'type_text',
            'kind_text',
            'num_of_bedrooms',
            'space_of_estate',
            'places_id',
        ]);

        if ($estate->relationLoaded('place') && $estate->place) {
            $data['place'] = $estate->place->only(['id', 'name', 'cities_id']);

            if ($estate->place->relationLoaded('city') && $estate->place->city) {
                $data['place']['city'] = $estate->place->city->only(['id', 'name']);
            }
        }

        if ($estate->relationLoaded('images')) {
            $uploader = app(FileUploadService::class);
            $data['images'] = $estate->images
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'image_url' => $uploader->publicUrl($image->image),
                    'is_primary' => (bool) $image->is_primary,
                ])
                ->values()
                ->all();
        }

        return $data;
    }
}
