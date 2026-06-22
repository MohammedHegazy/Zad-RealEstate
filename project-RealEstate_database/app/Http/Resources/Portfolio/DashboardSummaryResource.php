<?php

namespace App\Http\Resources\Portfolio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Aggregated investor dashboard metrics (all portfolios).
 *
 * @mixin \Illuminate\Support\Fluent
 */
class DashboardSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_portfolios' => $this->resource['total_portfolios'],
            'total_investments' => $this->resource['total_investments'],
            'total_portfolio_value' => $this->resource['total_portfolio_value'],
            'expected_annual_income' => $this->resource['expected_annual_income'],
            'average_roi' => $this->resource['average_roi'],
            'best_performing_property' => $this->resource['best_performing_property'],
            'worst_performing_property' => $this->resource['worst_performing_property'],
            'investment_distribution' => $this->resource['investment_distribution'],
            'counts_by_status' => $this->resource['counts_by_status'],
            'total_items' => $this->resource['total_items'],
        ];
    }
}
