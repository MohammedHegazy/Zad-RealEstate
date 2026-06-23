<?php

namespace App\Http\Resources\Portfolio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PortfolioItem */
class PortfolioItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'portfolio_id' => $this->portfolio_id,
            'estate_id' => $this->estate_id,
            'status' => $this->status,
            'investment_amount' => $this->investment_amount,
            'notes' => $this->notes,
            'invested_at' => $this->invested_at?->toIso8601String(),
            'sold_at' => $this->sold_at?->toIso8601String(),
            'global_taken' => $this->global_taken ?? false,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'portfolio' => PortfolioResource::make($this->whenLoaded('portfolio')),
            'estate' => EstateSummaryResource::make($this->whenLoaded('estate')),
        ];
    }
}
