<?php

namespace Tests\Feature\Investment;

use App\Models\Estate;
use App\Models\InvestmentPortfolio;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInvestmentApi;
use Tests\TestCase;

class InvestorDashboardTest extends TestCase
{
    use InteractsWithInvestmentApi;
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->getJson($this->apiPrefix().'/investor/dashboard')->assertUnauthorized();
    }

    public function test_user_can_view_investor_dashboard(): void
    {
        $user = User::factory()->create();
        $portfolio = InvestmentPortfolio::factory()->forUser($user)->default()->create();
        $estate = Estate::factory()->create([
            'status' => 'active',
            'price' => 300_000,
            'monthly_rent' => 2_000,
        ]);
        PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($estate)->invested(300_000)->create();

        $response = $this->actingAsApiUser($user)->getJson($this->apiPrefix().'/investor/dashboard');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.total_investments', 300_000);
        $response->assertJsonStructure([
            'data' => [
                'total_portfolios',
                'total_investments',
                'total_portfolio_value',
                'expected_annual_income',
                'average_roi',
                'best_performing_property',
                'worst_performing_property',
                'investment_distribution',
                'counts_by_status',
                'total_items',
            ],
        ]);
    }
}
