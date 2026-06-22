<?php

namespace App\Http\Resources\Portfolio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\InvestmentPortfolio */
class PortfolioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'target_budget' => $this->target_budget,
            'risk_level' => $this->risk_level,
            'status' => $this->status,
            'is_default' => $this->is_default,
            'properties_count' => $this->whenCounted('properties'),
            'total_invested' => $this->when(
                isset($this->total_invested),
                fn () => $this->total_invested,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
