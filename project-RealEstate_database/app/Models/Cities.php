<?php

namespace App\Models;



use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cities extends Model
{
    use HasFactory;

    protected static function newFactory(): CityFactory
    {
        return CityFactory::new();
    }

    protected $fillable = [
        'name',
        'image',
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


    public function places(): HasMany
    {
        return $this->hasMany(Places::class);
    }
}
