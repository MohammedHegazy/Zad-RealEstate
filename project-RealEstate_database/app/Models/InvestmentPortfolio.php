<?php

namespace App\Models;

use Database\Factories\PortfolioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentPortfolio extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_CLOSED = 'closed';

    protected $table = 'investment_portfolios';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_budget',
        'risk_level',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'target_budget' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public static function statuses(): array
    {
        return config('realestate.portfolio_statuses', [
            self::STATUS_ACTIVE,
            self::STATUS_ARCHIVED,
            self::STATUS_CLOSED,
        ]);
    }

    public static function riskLevels(): array
    {
        return config('realestate.portfolio_risk_levels', [
            'low',
            'moderate',
            'high',
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(PortfolioProperty::class, 'portfolio_id');
    }

    /** @deprecated Use properties() */
    public function items(): HasMany
    {
        return $this->properties();
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    protected static function newFactory(): PortfolioFactory
    {
        return PortfolioFactory::new();
    }
}
