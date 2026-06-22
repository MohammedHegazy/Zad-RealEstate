<?php

namespace App\Http\Controllers\Api\Investor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Portfolio\DashboardSummaryResource;
use App\Models\Portfolio;
use App\Services\Investment\InvestorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestorDashboardController extends BaseApiController
{
    public function __construct(
        private readonly InvestorDashboardService $dashboard,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Portfolio::class);

        $payload = $this->dashboard->getSummary($request->user());

        return $this->successResponse(
            (new DashboardSummaryResource($payload))->resolve(),
            'Dashboard summary retrieved.',
        );
    }
}
