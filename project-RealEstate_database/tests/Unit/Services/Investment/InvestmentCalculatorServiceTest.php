<?php

namespace Tests\Unit\Services\Investment;

use App\Models\Estate;
use App\Models\User;
use App\Services\Investment\InvestmentCalculatorService;
use Database\Factories\EstateFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvestmentCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(InvestmentCalculatorService::class);
    }

    public function test_calculate_returns_roi_and_cash_flow_metrics(): void
    {
        $metrics = $this->calculator->calculate(
            monthlyRent: 2_000,
            occupancyRate: 100,
            annualExpenses: 2_000,
            annualMaintenance: 1_000,
            annualPropertyTax: 1_000,
            purchasePrice: 300_000,
            annualHoaOrService: 500,
        );

        $this->assertSame(19_500.0, $metrics->expectedAnnualIncome);
        $this->assertEqualsWithDelta(1_625.0, $metrics->monthlyIncome, 0.01);
        $this->assertSame(19_500.0, $metrics->netProfit);
        $this->assertEqualsWithDelta(1_625.0, $metrics->cashFlow, 0.01);
        $this->assertEqualsWithDelta(6.5, $metrics->roi, 0.0001);
        $this->assertEqualsWithDelta(15.38, $metrics->paybackPeriod, 0.01);
    }

    public function test_calculate_for_estate_uses_estate_fields(): void
    {
        $estate = $this->makeEstate([
            'price' => 500_000,
            'monthly_rent' => 3_000,
            'annual_expenses' => 3_600,
            'maintenance_cost' => 1_200,
            'annual_property_tax' => 2_400,
            'annual_hoa_or_service' => 600,
            'occupancy_rate' => 90,
        ]);

        $metrics = $this->calculator->calculateForEstate($estate);

        $this->assertGreaterThan(0, $metrics->expectedAnnualIncome);
        $this->assertNotNull($metrics->roi);
        $this->assertNotNull($metrics->paybackPeriod);
    }

    public function test_calculate_for_analysis_storage_shape(): void
    {
        $result = $this->calculator->calculateForAnalysisStorage([
            'property_price' => 250_000,
            'monthly_rent' => 1_500,
            'annual_expenses' => 1_000,
            'maintenance_cost' => 500,
            'tax_cost' => 500,
            'occupancy_rate' => 100,
        ]);

        $this->assertArrayHasKey('expected_annual_income', $result);
        $this->assertArrayHasKey('roi', $result);
        $this->assertArrayHasKey('payback_period', $result);
        $this->assertArrayHasKey('monthly_income', $result);
        $this->assertArrayHasKey('net_profit', $result);
        $this->assertArrayHasKey('cash_flow', $result);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeEstate(array $overrides = []): Estate
    {
        $owner = User::factory()->create();

        return Estate::factory()
            ->forOwner($owner)
            ->create(array_merge(['status' => 'active'], $overrides));
    }
}
