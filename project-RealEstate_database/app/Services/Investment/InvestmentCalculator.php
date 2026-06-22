<?php

namespace App\Services\Investment;

use App\Models\Estate;
use App\Models\InvestmentAnalysis;

/**
 * @deprecated Use {@see InvestmentCalculatorService} instead.
 */
class InvestmentCalculator
{
    public function __construct(
        private readonly InvestmentCalculatorService $calculator,
    ) {}

    public function calculate(
        float $monthlyRent = 0,
        float $occupancyRate = 100,
        float $annualExpenses = 0,
        float $annualMaintenance = 0,
        float $annualPropertyTax = 0,
        float $purchasePrice = 0,
        float $annualHoaOrService = 0,
    ): InvestmentMetrics {
        return $this->calculator->calculate(
            $monthlyRent,
            $occupancyRate,
            $annualExpenses,
            $annualMaintenance,
            $annualPropertyTax,
            $purchasePrice,
            $annualHoaOrService,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{expected_annual_income: float, roi: ?float, payback_period: ?float}
     */
    public function calculateForEstate(array $data): array
    {
        return $this->calculator->calculateFromEstateArray($data)->toArray();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{expected_annual_income: float, roi: ?float, payback_period: ?float}
     */
    public function calculateForAnalysis(array $data): array
    {
        return $this->calculator->calculateForAnalysisStorage($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyToEstatePayload(array &$data, ?Estate $estate = null): void
    {
        $this->calculator->applyToEstatePayload($data, $estate);
    }

    /**
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     */
    public function mergeEstateInputs(array $patch, Estate $estate): array
    {
        return $this->calculator->mergeEstateInputs($patch, $estate);
    }

    /**
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     */
    public function mergeAnalysisInputs(array $patch, InvestmentAnalysis $analysis): array
    {
        return $this->calculator->mergeAnalysisInputs($patch, $analysis);
    }
}
