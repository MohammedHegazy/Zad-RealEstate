<?php

namespace App\Models;

use App\Models\Concerns\HasModerationStatus;
use App\Models\Concerns\HasReviewModerationAudit;
use Database\Factories\CompanyReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyReview extends Model
{
    use HasFactory, HasModerationStatus, HasReviewModerationAudit;

    protected static function newFactory(): CompanyReviewFactory
    {
        return CompanyReviewFactory::new();
    }

    protected $fillable = [
        'user_id',
        'company_id',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
}
