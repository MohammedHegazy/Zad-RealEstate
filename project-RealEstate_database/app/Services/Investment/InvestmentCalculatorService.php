<?php

namespace App\Services\Investment;

use App\Models\Estate;
use App\Models\InvestmentAnalysis;

/**
 * Dedicated investment calculation service — ROI, income, cash flow from estate or analysis data.
 */
class InvestmentCalculatorService
{
    /**
     * @var list<string>
     */
     //الحقول المطلوبة لحساب الاستثمار في العقار
    private const ESTATE_INPUT_KEYS = [
        'price',
        'monthly_rent',
        'occupancy_rate',
        'annual_expenses',
        'maintenance_cost',
        'annual_property_tax',
        'annual_hoa_or_service',
    ];

    /**
     * @var list<string>
     */
    //الحقول المطلوبة لحساب الاستثمار في التحليل
    private const ANALYSIS_INPUT_KEYS = [
        'property_price',
        'monthly_rent',
        'annual_expenses',
        'maintenance_cost',
        'tax_cost',
        'occupancy_rate',
    ];
        //calculate احسب الاستثمار في العقار
        //يقوم بحساب الاستثمار في العقار بناء على الحقول المطلوبة
    public function calculate(
        float $monthlyRent = 0,
        float $occupancyRate = 100,
        float $annualExpenses = 0,
        float $annualMaintenance = 0,
        float $annualPropertyTax = 0,
        float $purchasePrice = 0,
        float $annualHoaOrService = 0,
    ): InvestmentMetrics {
        return $this->calculateCore(
            $monthlyRent,
            $occupancyRate,
            $annualExpenses,
            $annualMaintenance,
            $annualPropertyTax,
            $purchasePrice,
            $annualHoaOrService,
        );
    }

    public function calculateForEstate(Estate $estate): InvestmentMetrics
    {
        return $this->calculateFromEstateArray($estate->only(self::ESTATE_INPUT_KEYS));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function calculateFromEstateArray(array $data): InvestmentMetrics
    {
        return $this->calculateFromNormalized($this->normalizeEstateArray($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * يقوم بحساب الاستثمار في التحليل بناء على الحقول المطلوبة
     * يقوم بتحويل البيانات المطلوبة لحساب الاستثمار في التحليل بناء على الحقول المطلوبة
     * calculateFromNormalized يقوم بحساب الاستثمار في التحليل بناء على الحقول المطلوبة
     * normalizeAnalysisArray يقوم بتحويل البيانات المطلوبة لحساب الاستثمار في التحليل بناء على الحقول المطلوبة
     * calculateCore يقوم بحساب الاستثمار في التحليل بناء على الحقول المطلوبة
     * InvestmentMetrics يقوم بحساب الاستثمار في التحليل بناء على الحقول المطلوبة
     */
    public function calculateForAnalysis(array $data): InvestmentMetrics 
    {
        return $this->calculateFromNormalized($this->normalizeAnalysisArray($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     expected_annual_income: float,
     *     roi: ?float,
     *     payback_period: ?float,
     *     monthly_income: float,
     *     net_profit: float,
     *     cash_flow: float
     * }
     */
    public function calculateForAnalysisStorage(array $data): array
    {
        return $this->calculateForAnalysis($data)->toArray();
    }

    /**
     * يقوم بحساب الاستثمار في العقار بناء على الحقول المطلوبة
     * أي أن العقار يُحفظ ومعه الحسابات الاستثمارية جاهزة.
     * الحساب هنا
     * array &$data يمثل بيانات العقار القادمة من request from api 
     */
    public function applyToEstatePayload(array &$data, ?Estate $estate = null): void 
    {
        if ($estate !== null) { 
            //يقوم بدمج البيانات المطلوبة لحساب الاستثمار في العقار بناء على الحقول المطلوعة
           //mergeEstateInputs يقوم بدمج البيانات المطلوبة لحساب الاستثمار في العقار بناء على الحقول المطلوعة
           //// دمج الحقول الاستثمارية غير المرسلة في الطلب

           $data = $this->mergeEstateInputs($data, $estate);
        }   

        $metrics = $this->calculateFromEstateArray($data); //يقوم بحساب الاستثمار في العقار بناء على الحقول المطلوبة

        $data['expected_annual_income'] = $metrics->expectedAnnualIncome;
        $data['roi'] = $metrics->roi;
        $data['payback_period'] = $metrics->paybackPeriod;
    }

    /**
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     * قد يرسل المستخدم:  'monthly_rent' => 6000
     * لكن الحساب يحتاج باقي القيم.
     * يتم أخذه من قاعدة البيانات. اذا لم بتم العثور على الطلب الذي يأتس من 
     * array_key_exists
     */
    public function mergeEstateInputs(array $patch, Estate $estate): array
    {
        foreach (self::ESTATE_INPUT_KEYS as $key) {
            if (! array_key_exists($key, $patch)) {
                $patch[$key] = $estate->getAttribute($key);
            }
        }

        return $patch;
    }

    /**
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     */
    public function mergeAnalysisInputs(array $patch, InvestmentAnalysis $analysis): array
    {
        foreach (self::ANALYSIS_INPUT_KEYS as $key) {
            if (! array_key_exists($key, $patch)) {
                $patch[$key] = $analysis->getAttribute($key);
            }
        }

        return $patch;
    }
    //يقوم بحساب الاستثمار في العقار بناء على الحقول المطلوبة
    //calculateCore تنفيذ المعادلات
    private function calculateFromNormalized(array $normalized): InvestmentMetrics 
    {
        return $this->calculateCore(
            $normalized['monthlyRent'],
            $normalized['occupancyRate'],
            $normalized['annualExpenses'],
            $normalized['annualMaintenance'],
            $normalized['annualPropertyTax'],
            $normalized['purchasePrice'],
            $normalized['annualHoaOrService'],
        );
    }
    //المعادلات
    private function calculateCore(
        float $monthlyRent,
        float $occupancyRate,
        float $annualExpenses,
        float $annualMaintenance,
        float $annualPropertyTax,
        float $purchasePrice,
        float $annualHoaOrService,
    ): InvestmentMetrics {
        $grossAnnual = $monthlyRent * 12 * ($occupancyRate / 100);
        $totalAnnualCosts = $annualExpenses + $annualMaintenance + $annualPropertyTax + $annualHoaOrService;
        $netProfit = $grossAnnual - $totalAnnualCosts;
        $expectedAnnualIncome = $netProfit > 0 ? round($netProfit, 2) : 0.0;
        $monthlyIncome = round($expectedAnnualIncome / 12, 2);
        $cashFlow = $monthlyIncome;

        $roi = null;
        $paybackPeriod = null;

        if ($purchasePrice > 0 && $expectedAnnualIncome > 0) {
            $roi = round(($expectedAnnualIncome / $purchasePrice) * 100, 4);
            $paybackPeriod = round($purchasePrice / $expectedAnnualIncome, 2);
        }
        //هذا الكائن يمثل النتيجة النهائية.
        return new InvestmentMetrics(
            expectedAnnualIncome: $expectedAnnualIncome,
            roi: $roi,
            paybackPeriod: $paybackPeriod,
            monthlyIncome: $monthlyIncome,
            netProfit: round($netProfit, 2),
            cashFlow: $cashFlow,
        );
    }

    /**
     * normalizeEstateArray يقوم بتوحيد البيانات المطلوبة لحساب الاستثمار في العقار
     * تمنع تكرار منطق الحساب.
     */
    private function normalizeEstateArray(array $data): array
    {
        return [
            'monthlyRent' => (float) ($data['monthly_rent'] ?? $data['estimated_monthly_rent'] ?? 0),
            'occupancyRate' => (float) ($data['occupancy_rate'] ?? 100),
            'annualExpenses' => (float) ($data['annual_expenses'] ?? 0),
            'annualMaintenance' => (float) ($data['maintenance_cost'] ?? $data['average_maintenance_cost'] ?? 0),
            'annualPropertyTax' => (float) ($data['annual_property_tax'] ?? 0),
            'purchasePrice' => (float) ($data['price'] ?? 0),
            'annualHoaOrService' => (float) ($data['annual_hoa_or_service'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     monthlyRent: float,
     *     occupancyRate: float,
     *     annualExpenses: float,
     *     annualMaintenance: float,
     *     annualPropertyTax: float,
     *     purchasePrice: float,
     *     annualHoaOrService: float
     * }
     */
    private function normalizeAnalysisArray(array $data): array
    {
        return [
            'monthlyRent' => (float) ($data['monthly_rent'] ?? 0),
            'occupancyRate' => (float) ($data['occupancy_rate'] ?? 100),
            'annualExpenses' => (float) ($data['annual_expenses'] ?? 0),
            'annualMaintenance' => (float) ($data['maintenance_cost'] ?? 0),
            'annualPropertyTax' => (float) ($data['tax_cost'] ?? 0),
            'purchasePrice' => (float) ($data['property_price'] ?? 0),
            'annualHoaOrService' => 0.0,
        ];
    }
}
