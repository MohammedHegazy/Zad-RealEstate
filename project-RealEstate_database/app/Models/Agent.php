<?php

namespace App\Models;

use App\Models\Concerns\HasModerationStatus;
use App\Models\Concerns\HasSocialLinks;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Database\Factories\AgentFactory;

class Agent extends Model
{
    use HasFactory, HasModerationStatus, HasSocialLinks;
    //HasSocialLinks: للربط بجدول social_links للتعامل مع الروابط الاجتماعية
    //HasFactory: للانشاء التلقائي للبيانات الوهمية
    protected static function newFactory(): AgentFactory
    {
        return AgentFactory::new();
    }

    protected $fillable = [
        'user_id',
        'companies_id',
        'profile_image',
        'views',
        'shares',
        'trust_score',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'shares' => 'integer',
            'trust_score' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'companies_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AgentReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->approved();
    }
}
