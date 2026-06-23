<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasSocialLinks;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasSocialLinks, Notifiable;

    public function isAdmin(): bool
    {
        return in_array($this->type, config('realestate.admin_types', ['admin']), true);
    }

         
    protected $fillable = [
        'username',
        'fname',
        'lname',
        'status',
        'type',
        'is_verified',
        'email',
        'password',
        'phone',
        'country_code_phone',
        'gender',
        'last_activity_at',
    ];

    /**
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public function isOnline(): bool
    {
        return $this->last_activity_at !== null
            && $this->last_activity_at->gt(now()->subMinutes(5));
    }

    public function lastSeenAgo(): ?string
    {
        if ($this->last_activity_at === null) return null;
        return $this->last_activity_at->diffForHumans();
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    
    public function estates(): HasMany
    {
        return $this->hasMany(Estate::class);
    }

    
    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(Notifications::class);
    }

    
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function favoriteEstates(): HasMany
    {
        return $this->hasMany(Favorit_estate::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Companies::class);
    }

    public function favoriteAgents(): HasMany
    {
        return $this->hasMany(Favorit_agent::class);
    }


    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function investmentAnalyses(): HasMany
    {
        return $this->hasMany(InvestmentAnalysis::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(InvestmentPortfolio::class);
    }

    public function investmentPortfolios(): HasMany
    {
        return $this->portfolios();
    }

    public function portfolioItems(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(PortfolioProperty::class, InvestmentPortfolio::class, 'user_id', 'portfolio_id');
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function propertyInteractions(): HasMany
    {
        return $this->hasMany(PropertyInteraction::class);
    }

    public function propertyReviews(): HasMany
    {
        return $this->hasMany(PropertyReview::class);
    }

    public function agentReviews(): HasMany
    {
        return $this->hasMany(AgentReview::class);
    }

    public function companyReviews(): HasMany
    {
        return $this->hasMany(CompanyReview::class);
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }
}
