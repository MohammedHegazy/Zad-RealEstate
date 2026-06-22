<?php

namespace Tests\Feature\Investment;

use App\Models\Estate;
use App\Models\InvestmentPortfolio;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInvestmentApi;
use Tests\TestCase;

class InvestmentPortfolioTest extends TestCase
{
    use InteractsWithInvestmentApi;
    use RefreshDatabase;

    public function test_user_can_create_and_list_portfolios(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAsApiUser($user)->postJson($this->apiPrefix().'/investment-portfolios', [
            'name' => 'Rental Portfolio',
            'description' => 'Long-term rentals',
            'target_budget' => 750_000,
            'risk_level' => 'moderate',
            'status' => 'active',
        ]);

        $this->assertApiSuccess($create, 201);
        $create->assertJsonPath('data.name', 'Rental Portfolio');
        $create->assertJsonPath('data.target_budget', '750000.00');

        $list = $this->actingAsApiUser($user)->getJson($this->apiPrefix().'/investment-portfolios');

        $this->assertApiSuccess($list);
        $list->assertJsonCount(1, 'data');
    }

    public function test_user_can_add_and_list_portfolio_properties(): void
    {
        $user = User::factory()->create();
        $portfolio = InvestmentPortfolio::factory()->forUser($user)->create();
        $estate = Estate::factory()->create(['status' => 'active']);

        $add = $this->actingAsApiUser($user)->postJson(
            $this->apiPrefix().'/investment-portfolios/'.$portfolio->id.'/properties',
            [
                'estate_id' => $estate->id,
                'status' => 'invested',
                'investment_amount' => 150_000,
            ]
        );

        $this->assertApiSuccess($add, 201);

        $list = $this->actingAsApiUser($user)->getJson(
            $this->apiPrefix().'/investment-portfolios/'.$portfolio->id.'/properties'
        );

        $this->assertApiSuccess($list);
        $list->assertJsonCount(1, 'data.properties');
        $list->assertJsonPath('data.total_invested', 150_000);
    }

    public function test_user_can_remove_property_from_portfolio(): void
    {
        $user = User::factory()->create();
        $portfolio = InvestmentPortfolio::factory()->forUser($user)->create();
        $estate = Estate::factory()->create(['status' => 'active']);
        $property = PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($estate)->tracking()->create();

        $response = $this->actingAsApiUser($user)->deleteJson(
            $this->apiPrefix().'/investment-portfolios/'.$portfolio->id.'/properties/'.$property->id
        );

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('portfolio_properties', ['id' => $property->id]);
    }

    public function test_portfolio_show_includes_total_invested(): void
    {
        $user = User::factory()->create();
        $portfolio = InvestmentPortfolio::factory()->forUser($user)->create();
        $estate = Estate::factory()->create(['status' => 'active']);
        PortfolioItem::factory()->forPortfolio($portfolio)->forEstate($estate)->invested(120_000)->create();

        $response = $this->actingAsApiUser($user)->getJson(
            $this->apiPrefix().'/investment-portfolios/'.$portfolio->id
        );

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.total_invested', 120_000);
    }
}
