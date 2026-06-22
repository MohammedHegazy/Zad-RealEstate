<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreInvestmentAnalysisRequest;
use App\Http\Requests\UpdateInvestmentAnalysisRequest;
use App\Models\Estate;
use App\Models\InvestmentAnalysis;
use App\Services\Investment\InvestmentCalculatorService;
use App\Traits\FormatsInvestmentAnalysisResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestmentAnalysisController extends BaseApiController
{
    //يقوم بتنسيق الرد 
    use FormatsInvestmentAnalysisResponse;
    //هنا Laravel يقوم بحقن الخدمة:
    public function __construct(
        private readonly InvestmentCalculatorService $calculator,
    ) {}

    /**
     * List the authenticated user's saved investment analyses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->investmentAnalyses()
            ->with('estate:id,name,price,status,monthly_rent,type_text,kind_text');

        if ($request->filled('estate_id')) {
            $query->where('estate_id', $request->integer('estate_id'));
        }

        $analyses = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            collect($analyses->items())
                ->map(fn (InvestmentAnalysis $a) => $this->formatInvestmentAnalysis($a))
                ->values()
                ->all(),
            'Investment analyses retrieved.',
            200,
            $this->paginationMeta($analyses)
        );
    }

    public function store(StoreInvestmentAnalysisRequest $request): JsonResponse
    {
        $analysis = $this->createAnalysis(
            $request,
            (int) $request->validated('estate_id'),
            $request->validated()
        );

        return $this->createdResponse(
            $this->formatInvestmentAnalysis($analysis),//يقوم بتنسيق الرد
            'Investment analysis saved.'//يقوم بعرض الرد
        );
    }

    
    public function storeByEstate(StoreInvestmentAnalysisRequest $request, Estate $estate): JsonResponse
    {
        $input = $request->validated();
        $input['property_price'] = $input['property_price'] ?? $estate->price;

        if (empty($input['property_price']) || (float) $input['property_price'] <= 0) {
            return $this->errorResponse('Property price is required when the estate has no listed price.', 422);
        }

        $input['monthly_rent'] = $input['monthly_rent'] ?? $estate->monthly_rent;
        $input['annual_expenses'] = $input['annual_expenses'] ?? $estate->annual_expenses;
        $input['maintenance_cost'] = $input['maintenance_cost'] ?? $estate->maintenance_cost;
        $input['tax_cost'] = $input['tax_cost'] ?? $estate->annual_property_tax;
        $input['occupancy_rate'] = $input['occupancy_rate'] ?? $estate->occupancy_rate ?? 100;

        $analysis = $this->createAnalysis($request, $estate->id, $input);

        return $this->createdResponse(
            $this->formatInvestmentAnalysis($analysis),
            'Investment analysis saved.'
        );
    }

    public function show(Request $request, InvestmentAnalysis $investmentAnalysis): JsonResponse
    {
        if (! $this->ownsAnalysis($request, $investmentAnalysis)) {
            return $this->notFoundResponse('Investment analysis not found.');
        }

        $investmentAnalysis->load('estate:id,name,price,status,monthly_rent,type_text,kind_text');

        return $this->successResponse(
            $this->formatInvestmentAnalysis($investmentAnalysis),
            'Investment analysis retrieved.'
        );
    }

    public function update(UpdateInvestmentAnalysisRequest $request, InvestmentAnalysis $investmentAnalysis): JsonResponse
    {
        if (! $this->ownsAnalysis($request, $investmentAnalysis)) {
            return $this->notFoundResponse('Investment analysis not found.');
        }

        $input = array_merge(
            $investmentAnalysis->only([
                'property_price',
                'monthly_rent',
                'annual_expenses',
                'maintenance_cost',
                'tax_cost',
                'occupancy_rate',
            ]),
            $request->validated()
        );

        $metrics = $this->calculator->calculateForAnalysisStorage($input);
        // يحفظ البيانات الجديدة في القاعدة بعد التحديث
        $investmentAnalysis->update(array_merge(
            $request->validated(),
            [
                'expected_annual_income' => $metrics['expected_annual_income'],
                'roi' => $metrics['roi'],
                'payback_period' => $metrics['payback_period'],
            ]
        ));

        return $this->successResponse(
            $this->formatInvestmentAnalysis(
                $investmentAnalysis->fresh()->load('estate:id,name,price,status,monthly_rent,type_text,kind_text')
            ),
            'Investment analysis updated successfully.'
        );
    }

    public function destroy(Request $request, InvestmentAnalysis $investmentAnalysis): JsonResponse
    {
        if (! $this->ownsAnalysis($request, $investmentAnalysis)) {
            return $this->notFoundResponse('Investment analysis not found.');
        }

        $investmentAnalysis->delete();

        return $this->deletedResponse('Investment analysis deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createAnalysis(Request $request, int $estateId, array $input): InvestmentAnalysis
    {
        $metrics = $this->calculator->calculateForAnalysisStorage($input);

        $analysis = InvestmentAnalysis::create([
            'user_id' => $request->user()->id,
            'estate_id' => $estateId,
            'property_price' => $input['property_price'],
            'monthly_rent' => $input['monthly_rent'] ?? 0,
            'annual_expenses' => $input['annual_expenses'] ?? 0,
            'maintenance_cost' => $input['maintenance_cost'] ?? 0,
            'tax_cost' => $input['tax_cost'] ?? 0,
            'occupancy_rate' => $input['occupancy_rate'] ?? 100,
            'expected_annual_income' => $metrics['expected_annual_income'],
            'roi' => $metrics['roi'],
            'payback_period' => $metrics['payback_period'],
        ]);

        return $analysis->load('estate:id,name,price,status,monthly_rent,type_text,kind_text');
    }

    private function ownsAnalysis(Request $request, InvestmentAnalysis $analysis): bool
    {
        return $analysis->user_id === $request->user()->id;
    }
}
