<?php

namespace App\Traits;

use App\Models\Estate;
use App\Models\InvestmentAnalysis;

trait FormatsInvestmentAnalysisResponse
{
    protected function formatInvestmentAnalysis(InvestmentAnalysis $analysis): array
    {
        $data = $analysis->only([
            'id',
            'user_id',
            'estate_id',
            'property_price',
            'monthly_rent',
            'annual_expenses',
            'maintenance_cost',
            'tax_cost',
            'occupancy_rate',
            'expected_annual_income',
            'roi',
            'payback_period',
            'created_at',
            'updated_at',
        ]);

        if ($analysis->relationLoaded('estate') && $analysis->estate) {
            $data['estate'] = $this->formatAnalysisEstate($analysis->estate);
        }

        return $data;
    }

    protected function formatAnalysisEstate(Estate $estate): array
    {
        return $estate->only([
            'id',
            'name',
            'price',
            'status',
            'monthly_rent',
            'type_text',
            'kind_text',
        ]);
    }
}
