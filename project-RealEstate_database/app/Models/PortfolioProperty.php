<?php

namespace App\Models;

use Database\Factories\PortfolioItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProperty extends Model
{
    use HasFactory;

    public const STATUS_TRACKING = 'tracking';

    public const STATUS_INVESTED = 'invested';

    public const STATUS_SOLD = 'sold';

    protected $table = 'portfolio_properties';

    protected $fillable = [
        'portfolio_id',
        'estate_id',
        'status',
        'investment_amount',
        'notes',
        'invested_at',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'investment_amount' => 'decimal:2',
            'invested_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return config('realestate.portfolio_item_statuses', [
            self::STATUS_TRACKING,
            self::STATUS_INVESTED,
            self::STATUS_SOLD,
        ]);
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(InvestmentPortfolio::class, 'portfolio_id');
    }

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTracking($query)
    {
        return $query->where('status', self::STATUS_TRACKING);
    }

    public function scopeInvested($query)
    {
        return $query->where('status', self::STATUS_INVESTED);
    }

    public function scopeSold($query)
    {
        return $query->where('status', self::STATUS_SOLD);
    }

    public function isTracking(): bool
    {
        return $this->status === self::STATUS_TRACKING;
    }

    public function isInvested(): bool
    {
        return $this->status === self::STATUS_INVESTED;
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }

    protected static function newFactory(): PortfolioItemFactory
    {
        return PortfolioItemFactory::new();
    }
}
