<?php

namespace App\Models;

use App\Enums\InteractionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'estate_id',
        'interaction_type',
        'interaction_score',
    ];

    protected function casts(): array
    {
        return [
            'interaction_type' => InteractionType::class,
            'interaction_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }
}
