<?php

namespace Tests\Unit\Services\Investment;

use App\Models\Estate;
use App\Models\InvestmentPortfolio;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\Investment\InvestorDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvestorDashboardService $dashboard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboard = app(InvestorDashboardService::class);
    }

    public function test_dashboard_summary_includes_required_metrics(): void
    {
        $user = User::factory()->create();
        $portfolio = InvestmentPortfolio::factory()->forUser($user)->default()->create();

        $highRoiEstate = Estate::factory()->create([
            'status' => 'active',
            'price' => 200_000,
            'monthly_rent' => 2_500,
            'annual_expenses' => 1_000,
            'maintenance_cost' => 500,
            'annual_property_tax' => 500,
        ]);

        $lowRoiEstate = Estate::factory()->create([
            'status' => 'active',
            'price' => 400_000,
            'monthly_rent' => 1_000,
            'annual_expenses' => 2_000,
            'maintenance_cost' => 1_000,
            'annual_property_tax' => 1_000,
        ]);

        PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($highRoiEstate)->invested(200_000)->create();
        PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($lowRoiEstate)->invested(400_000)->create();

        $summary = $this->dashboard->getSummary($user);

        $this->assertSame(1, $summary['total_portfolios']);
        $this->assertSame(600_000.0, $summary['total_investments']);
        $this->assertGreaterThan(0, $summary['expected_annual_income']);
        $this->assertNotNull($summary['average_roi']);
        $this->assertNotNull($summary['best_performing_property']);
        $this->assertNotNull($summary['worst_performing_property']);
        $this->assertArrayHasKey('by_portfolio', $summary['investment_distribution']);
        $this->assertArrayHasKey('by_status', $summary['investment_distribution']);
    }
}
