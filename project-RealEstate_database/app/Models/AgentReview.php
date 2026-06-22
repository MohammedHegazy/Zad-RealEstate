<?php

namespace App\Models;

use App\Models\Concerns\HasModerationStatus;
use App\Models\Concerns\HasReviewModerationAudit;
use Database\Factories\AgentReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentReview extends Model
{
    use HasFactory, HasModerationStatus, HasReviewModerationAudit;

    protected static function newFactory(): AgentReviewFactory
    {
        return AgentReviewFactory::new();
    }

    protected $fillable = [
        'user_id',
        'agent_id',
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

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
