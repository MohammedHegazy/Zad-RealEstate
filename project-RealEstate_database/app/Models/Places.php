<?php

namespace App\Models;

use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Places extends Model
{
    use HasFactory;

    protected static function newFactory(): PlaceFactory
    {
        return PlaceFactory::new();
    }
    protected $fillable = [
        'cities_id',  
        'name',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }


    public function city(): BelongsTo
    {
        return $this->belongsTo(Cities::class, 'cities_id');
    }

    public function estates(): HasMany
    {
        return $this->hasMany(Estate::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Companies::class);
    }
}
