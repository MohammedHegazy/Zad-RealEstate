<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'estate_id',
        'property_price',
        'monthly_rent',
        'annual_expenses',
        'maintenance_cost',
        'tax_cost',
        'occupancy_rate',
        'expected_annual_income',
        'roi',
        'payback_period',
    ];

    protected function casts(): array
    {
        return [
            'property_price' => 'decimal:2',
            'monthly_rent' => 'decimal:2',
            'annual_expenses' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
            'tax_cost' => 'decimal:2',
            'occupancy_rate' => 'decimal:2',
            'expected_annual_income' => 'decimal:2',
            'roi' => 'decimal:4',
            'payback_period' => 'decimal:2',
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
