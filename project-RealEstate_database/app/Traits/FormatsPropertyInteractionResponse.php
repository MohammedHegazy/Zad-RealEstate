<?php

namespace App\Traits;

use App\Models\PropertyInteraction;

trait FormatsPropertyInteractionResponse
{
    protected function formatPropertyInteraction(PropertyInteraction $interaction): array
    {
        $data = $interaction->only([
            'id',
            'user_id',
            'estate_id',
            'interaction_type',
            'interaction_score',
            'created_at',
            'updated_at',
        ]);

        if ($interaction->relationLoaded('estate') && $interaction->estate) {
            $data['estate'] = $interaction->estate->only([
                'id',
                'name',
                'price',
                'type_text',
                'kind_text',
                'places_id',
            ]);
        }

        return $data;
    }
}
