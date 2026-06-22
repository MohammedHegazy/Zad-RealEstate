<?php
//العقارات المقترحة للمستخدم بناءً على تحليل السلوك + التفضيلات + الذكاء الحسابي
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = [
        'user_id',
        'estate_id',
        'recommendation_score',
        'matching_percentage',
        'score_factors',
        'recommendation_reason',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'recommendation_score' => 'decimal:2',
            'matching_percentage' => 'decimal:2',
            'score_factors' => 'array',
            'recommendation_reason' => 'array',
            'is_active' => 'boolean',
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
