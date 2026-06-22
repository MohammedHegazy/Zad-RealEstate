<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricePrediction extends Model
{
    protected $fillable = [
        'user_id',
        'estate_id',
        'place_label',
        'input_features',
        'predicted_price',
        'listed_price',
        'price_difference',
        'price_difference_percent',
        'valuation_insight',
    ];

    protected function casts(): array
    {
        return [
            'input_features' => 'array',
            'predicted_price' => 'decimal:2',
            'listed_price' => 'decimal:2',
            'price_difference' => 'decimal:2',
            'price_difference_percent' => 'decimal:2',
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
