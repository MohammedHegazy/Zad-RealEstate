<?php

namespace App\Http\Resources\Trust;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'average_rating' => $this->resource['average_rating'] ?? null,
            'reviews_count' => $this->resource['reviews_count'] ?? 0,
        ];
    }
}
