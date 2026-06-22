<?php

namespace App\Http\Resources\Portfolio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Estate */
class EstateSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'type_text' => $this->type_text,
            'kind_text' => $this->kind_text,
            'price' => $this->price,
            'roi' => $this->roi,
            'expected_annual_income' => $this->expected_annual_income,
            'place' => $this->whenLoaded('place', fn () => [
                'id' => $this->place->id,
                'name' => $this->place->name,
            ]),
        ];
    }
}
