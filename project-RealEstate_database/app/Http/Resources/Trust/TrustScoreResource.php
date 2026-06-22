<?php

namespace App\Http\Resources\Trust;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrustScoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'entity_type' => $this->resource['entity_type'],
            'entity_id' => $this->resource['entity_id'],
            'trust_score' => $this->resource['trust_score'],
            'average_rating' => $this->resource['average_rating'],
            'reviews_count' => $this->resource['reviews_count'],
            'is_verified' => $this->resource['is_verified'],
            'factors' => $this->resource['factors'],
        ];
    }
}
