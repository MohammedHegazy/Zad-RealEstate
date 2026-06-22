<?php
// ماذا يريد المستخدم
namespace App\Models;

use App\Enums\InvestmentGoal;
use App\Enums\PropertyFunction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'cities_id',
        'places_id',
        'min_budget',
        'max_budget',
        'preferred_property_type',
        'preferred_rooms',
        'property_function',
        'investment_goal',
        'risk_level',
        'interests',
    ];

    protected function casts(): array
    {
        return [
            'min_budget' => 'decimal:2',
            'max_budget' => 'decimal:2',
            'preferred_rooms' => 'integer',
            'property_function' => PropertyFunction::class,
            'investment_goal' => InvestmentGoal::class,
            'interests' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(Cities::class, 'cities_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Places::class, 'places_id');
    }
}
