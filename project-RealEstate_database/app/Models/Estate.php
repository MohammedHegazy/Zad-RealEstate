<?php

namespace App\Models;

use App\Models\Concerns\HasSocialLinks;
use Database\Factories\EstateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estate extends Model
{
    use HasFactory, HasSocialLinks;

    protected static function newFactory(): EstateFactory
    {
        return EstateFactory::new();
    }

    protected $fillable = [
        'user_id',
        'places_id',
        'latitude',
        'longitude',
        'name',
        'phone',
        'country_code_phone',
        'space_of_estate',
        'price_of_meter',
        'price',
        'monthly_rent',
        'annual_expenses',
        'maintenance_cost',
        'annual_property_tax',
        'annual_hoa_or_service',
        'occupancy_rate',
        'expected_annual_income',
        'roi',
        'payback_period',
        'floor',
        'num_of_bedrooms',
        'num_of_livingrooms',
        'num_of_receptions',
        'num_of_bathrooms',
        'num_of_kitchens',
        'num_of_balconies',
        'status',
        'type_text',
        'kind_text',
        'is_furnished',
        'description',
        'real_number',
        'date_of_build',
        'state_of_build',
        'rent_kind',
        'rent_description',
        'views',
        'shares',
    ];

    protected function casts(): array
    {
        return [
            'space_of_estate' => 'decimal:2',
            'price_of_meter' => 'decimal:2',
            'price' => 'decimal:2',
            'monthly_rent' => 'decimal:2',
            'annual_expenses' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
            'annual_property_tax' => 'decimal:2',
            'annual_hoa_or_service' => 'decimal:2',
            'occupancy_rate' => 'decimal:2',
            'expected_annual_income' => 'decimal:2',
            'roi' => 'decimal:4',
            'payback_period' => 'decimal:2',
            'is_furnished' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    
     
    public function scopeWithinMapBounds($query, float $north, float $south, float $east, float $west)
    {
        return $query
            ->whereBetween('latitude', [$south, $north])
            ->whereBetween('longitude', [$west, $east]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Places::class, 'places_id');
    }

    public function ads(): HasMany
    {
        return $this->hasMany(EstateAd::class, 'estate_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EstateImage::class, 'estate_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(EstateVideo::class, 'estate_id');
    }

    public function favoriteByUsers(): HasMany
    {
        return $this->hasMany(Favorit_estate::class, 'estate_id');
    }

    public function investmentAnalyses(): HasMany
    {
        return $this->hasMany(InvestmentAnalysis::class);
    }

    public function pricePredictions(): HasMany
    {
        return $this->hasMany(PricePrediction::class);
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PropertyReview::class);
    }
}
