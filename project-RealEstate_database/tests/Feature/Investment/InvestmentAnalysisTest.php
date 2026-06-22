<?php

namespace Tests\Feature\Investment;

use App\Models\Estate;
use App\Models\InvestmentAnalysis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithInvestmentApi;
use Tests\TestCase;

class InvestmentAnalysisTest extends TestCase
{
    use InteractsWithInvestmentApi;
    use RefreshDatabase;

    public function test_guest_cannot_access_investment_analyses(): void
    {
        $this->getJson($this->apiPrefix().'/investment-analyses')->assertUnauthorized();
    }

    public function test_user_can_create_and_list_analyses(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active', 'price' => 300_000]);

        $create = $this->actingAsApiUser($user)->postJson($this->apiPrefix().'/investment-analyses', [
            'estate_id' => $estate->id,
            'property_price' => 300_000,
            'monthly_rent' => 2_000,
            'annual_expenses' => 1_000,
            'maintenance_cost' => 500,
            'tax_cost' => 500,
            'occupancy_rate' => 100,
        ]);

        $this->assertApiSuccess($create, 201);
        $create->assertJsonPath('data.expected_annual_income', '22000.00');

        $list = $this->actingAsApiUser($user)->getJson($this->apiPrefix().'/investment-analyses');

        $this->assertApiSuccess($list);
        $list->assertJsonCount(1, 'data.data');
    }

    public function test_user_can_show_own_analysis(): void
    {
        $user = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);

        $analysis = InvestmentAnalysis::query()->create([
            'user_id' => $user->id,
            'estate_id' => $estate->id,
            'property_price' => 250_000,
            'monthly_rent' => 1_500,
            'annual_expenses' => 500,
            'maintenance_cost' => 300,
            'tax_cost' => 200,
            'occupancy_rate' => 100,
            'expected_annual_income' => 16_000,
            'roi' => 6.4,
            'payback_period' => 15.63,
        ]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/investment-analyses/'.$analysis->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $analysis->id);
    }

    public function test_user_cannot_view_other_users_analysis(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $estate = Estate::factory()->create(['status' => 'active']);

        $analysis = InvestmentAnalysis::query()->create([
            'user_id' => $owner->id,
            'estate_id' => $estate->id,
            'property_price' => 250_000,
            'monthly_rent' => 1_500,
            'annual_expenses' => 0,
            'maintenance_cost' => 0,
            'tax_cost' => 0,
            'occupancy_rate' => 100,
            'expected_annual_income' => 18_000,
            'roi' => 7.2,
            'payback_period' => 13.89,
        ]);

        $this->actingAsApiUser($other)
            ->getJson($this->apiPrefix().'/investment-analyses/'.$analysis->id)
            ->assertNotFound();
    }
}
