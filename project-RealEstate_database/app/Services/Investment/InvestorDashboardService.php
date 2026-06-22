<?php

namespace App\Services\Investment;

use App\Models\InvestmentPortfolio;
use App\Models\PortfolioProperty;
use App\Models\User;
use Illuminate\Support\Collection;

class InvestorDashboardService
{
    public function __construct(
        private readonly InvestmentCalculatorService $calculator,
    ) {}

    /**
     * يقوم بجمع البيانات الاستثمارية للمستثمر بناء على البيانات المطلوبة من قاعدة البيانات
     * يضيف عقارات لمحفظة ثم يفتح لوحة التحكم 
    */
    public function getSummary(User $user): array
    {
        $portfolios = InvestmentPortfolio::query()
            ->forUser($user->id)
            ->withCount('properties')
            ->get();

        $properties = PortfolioProperty::query()
            ->whereHas('portfolio', fn ($q) => $q->where('user_id', $user->id))
            ->with('estate:id,name,price,roi,expected_annual_income,monthly_rent,occupancy_rate,annual_expenses,maintenance_cost,annual_property_tax,annual_hoa_or_service,status,type_text,kind_text')
            ->get();

        $statuses = PortfolioProperty::statuses();
        $countsByStatus = array_fill_keys($statuses, 0);

        $totalInvested = 0.0;
        $totalPortfolioValue = 0.0;
        $expectedAnnualIncome = 0.0;
        $weightedRoiNumerator = 0.0;
        $weightedRoiDenominator = 0.0;
        $byPortfolio = [];
        $byStatus = [];
        $propertyPerformances = [];

        foreach ($properties as $property) {
            $countsByStatus[$property->status] = ($countsByStatus[$property->status] ?? 0) + 1;

            $estate = $property->estate;
            if ($estate === null) {
                continue;
            }

            $metrics = $this->calculator->calculateForEstate($estate);
            $investedAmount = $property->investment_amount !== null
                ? (float) $property->investment_amount
                : (float) ($estate->price ?? 0);

            if ($property->status === PortfolioProperty::STATUS_INVESTED) {
                $totalInvested += $investedAmount;
                $expectedAnnualIncome += $metrics->expectedAnnualIncome;

                if ($metrics->roi !== null && $investedAmount > 0) {
                    $weightedRoiNumerator += $metrics->roi * $investedAmount;
                    $weightedRoiDenominator += $investedAmount;
                }
            }

            if ($property->status !== PortfolioProperty::STATUS_SOLD) {
                $totalPortfolioValue += (float) ($estate->price ?? $investedAmount);
            }

            $portfolioId = (string) $property->portfolio_id;
            $byPortfolio[$portfolioId] = ($byPortfolio[$portfolioId] ?? 0.0) + $investedAmount;
            $byStatus[$property->status] = ($byStatus[$property->status] ?? 0.0) + $investedAmount;

            if ($metrics->roi !== null) {
                $propertyPerformances[] = [
                    'property_id' => $property->id,
                    'estate_id' => $estate->id,
                    'estate_name' => $estate->name,
                    'status' => $property->status,
                    'investment_amount' => round($investedAmount, 2),
                    'roi' => $metrics->roi,
                    'expected_annual_income' => $metrics->expectedAnnualIncome,
                ];
            }
        }

        $averageRoi = $weightedRoiDenominator > 0
            ? round($weightedRoiNumerator / $weightedRoiDenominator, 4)
            : null;

        return [
            'total_portfolios' => $portfolios->count(),
            'total_investments' => round($totalInvested, 2),
            'total_portfolio_value' => round($totalPortfolioValue, 2),
            'expected_annual_income' => round($expectedAnnualIncome, 2),
            'average_roi' => $averageRoi,
            'best_performing_property' => $this->pickExtremeProperty($propertyPerformances, 'max'),
            'worst_performing_property' => $this->pickExtremeProperty($propertyPerformances, 'min'),
            'investment_distribution' => [
                'by_portfolio' => $this->labelPortfolioDistribution($portfolios, $byPortfolio),
                'by_status' => $byStatus,
            ],
            'counts_by_status' => $countsByStatus,
            'total_items' => array_sum($countsByStatus),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $performances
     * @return array<string, mixed>|null
     */
    private function pickExtremeProperty(array $performances, string $mode): ?array
    {
        if ($performances === []) {
            return null;
        }

        $collection = collect($performances);

        return $mode === 'max'
            ? $collection->sortByDesc('roi')->first()
            : $collection->sortBy('roi')->first();
    }

    /**
     * @param  Collection<int, InvestmentPortfolio>  $portfolios
     * @param  array<string, float>  $amounts
     * @return list<array<string, mixed>>
     */
    private function labelPortfolioDistribution(Collection $portfolios, array $amounts): array
    {
        return $portfolios->map(function (InvestmentPortfolio $portfolio) use ($amounts) {
            return [
                'portfolio_id' => $portfolio->id,
                'portfolio_name' => $portfolio->name,
                'invested_amount' => round($amounts[(string) $portfolio->id] ?? 0.0, 2),
            ];
        })->values()->all();
    }
}
