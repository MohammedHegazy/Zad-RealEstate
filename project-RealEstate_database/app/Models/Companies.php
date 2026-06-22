<?php

namespace App\Models;

use App\Models\Concerns\HasModerationStatus;
use App\Models\Concerns\HasSocialLinks;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Database\Factories\CompanyFactory;

class Companies extends Model
{
    use HasFactory, HasModerationStatus, HasSocialLinks;

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }


    protected $table = 'companies';

    protected $fillable = [
        'user_id',
        'places_id',
        'company_name',
        'website',//الموقع الالكتروني للشركة        
        'employees_num',//عدد الموظفين
        'description',//تفاصيل الشركة نشاطها-خدماتها-خبرتها
        'work_days',//ايام العمل — مصفوفة أيام مثل ["Sunday","Monday",...]
        'status',//pending | approved | rejected | suspended
        'profile_image',//صورة الشركة اللوغو
        'banner_image',//صورة الشركة الرئيسية
        'trust_score',//الموثوقية المرجعية للشركة
    ];

    protected function casts(): array
    {
        return [
            'employees_num' => 'integer',
            'work_days' => 'array',
            'trust_score' => 'integer',
        ];
    }
    // isApproved(), isPending(), isRejected(), isSuspended() from HasModerationStatus

    public function isPubliclyVisible(): bool
    {
        return $this->status === 'approved';
    }

    public function isWorkingDay(string $day): bool
    {
        $normalized = ucfirst(strtolower(trim($day)));

        return in_array($normalized, $this->work_days ?? [], true);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function place(): BelongsTo
    {
        return $this->belongsTo(Places::class, 'places_id');
    }

    
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'companies_id');
    }

    
    public function reviews(): HasMany
    {
        return $this->hasMany(CompanyReview::class, 'company_id');
    }
}
