<?php

namespace App\Providers;

// =============================================================================
// Service Provider: تسجيل خدمات التطبيق عند الإقلاع
// =============================================================================
// Laravel يحمّل هذا الملف تلقائياً من config/app.php.
// register(): ربط واجهات بكلاسات (Dependency Injection) قبل باقي التطبيق.
// boot(): إعدادات بعد تحميل كل شيء (مثل View composers، Gates، Observers).

use App\Models\AgentReview;
use App\Models\CompanyReview;
use App\Models\InvestmentPortfolio;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\PortfolioProperty;
use App\Models\PropertyReview;
use App\Models\VerificationRequest;
use App\Policies\AgentReviewPolicy;
use App\Policies\CompanyReviewPolicy;
use App\Policies\PortfolioItemPolicy;
use App\Policies\PortfolioPolicy;
use App\Policies\PropertyReviewPolicy;
use App\Policies\VerificationRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // register: مكان تسجيل Singletons و Bindings في حاوية Laravel (Container)
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    // boot: يُنفَّذ بعد register — مناسب لقواعد عامة، Macros، تعديلات Schema
    public function boot(): void
    {
        Gate::policy(Portfolio::class, PortfolioPolicy::class);
        Gate::policy(InvestmentPortfolio::class, PortfolioPolicy::class);
        Gate::policy(PortfolioItem::class, PortfolioItemPolicy::class);
        Gate::policy(PortfolioProperty::class, PortfolioItemPolicy::class);
        Gate::policy(PropertyReview::class, PropertyReviewPolicy::class);
        Gate::policy(AgentReview::class, AgentReviewPolicy::class);
        Gate::policy(CompanyReview::class, CompanyReviewPolicy::class);
        Gate::policy(VerificationRequest::class, VerificationRequestPolicy::class);
    }
}
