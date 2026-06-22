<?php
//تحليل سلوك المستخدم
namespace App\Models;

use App\Models\Concerns\HasModerationStatus;
use App\Models\Concerns\HasReviewModerationAudit;
use Database\Factories\PropertyReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyReview extends Model
{
    use HasFactory, HasModerationStatus, HasReviewModerationAudit;

    protected static function newFactory(): PropertyReviewFactory
    {
        return PropertyReviewFactory::new();
    }

    protected $fillable = [
        'user_id',
        'estate_id',
        'rating',
        'review',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'reviewed_at' => 'datetime',
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
