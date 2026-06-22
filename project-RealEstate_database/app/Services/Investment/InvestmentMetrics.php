<?php

namespace App\Services\Investment;

readonly class InvestmentMetrics
{
    public function __construct(
        public float $expectedAnnualIncome,
        public ?float $roi,
        public ?float $paybackPeriod,
        public float $monthlyIncome = 0.0,
        public float $netProfit = 0.0,
        public float $cashFlow = 0.0,
    ) {}

    /**
     * @return array{
     *     expected_annual_income: float,
     *     roi: ?float,
     *     payback_period: ?float,
     *     monthly_income: float,
     *     net_profit: float,
     *     cash_flow: float
     * }
     */
    public function toArray(): array
    {
        return [
            'expected_annual_income' => $this->expectedAnnualIncome,
            'roi' => $this->roi,
            'payback_period' => $this->paybackPeriod,
            'monthly_income' => $this->monthlyIncome,
            'net_profit' => $this->netProfit,
            'cash_flow' => $this->cashFlow,
        ];
    }
}
