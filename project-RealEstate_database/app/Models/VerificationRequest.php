<?php
//للمستخدم تقديم طلب توثيق يحتوي على مستندات رسمية يتم مراجعتها من قبل الأدمن. يتم تتبع حالة الطلب
namespace App\Models;

use App\Models\Concerns\HasModerationStatus;
use Database\Factories\VerificationRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationRequest extends Model
{
    use HasFactory, HasModerationStatus;

    protected static function newFactory(): VerificationRequestFactory
    {
        return VerificationRequestFactory::new();
    }

    protected $fillable = [
        'user_id',
        'document_type',
        'document_path',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
