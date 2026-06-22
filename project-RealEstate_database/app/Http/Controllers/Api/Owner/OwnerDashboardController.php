<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Owner\OwnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerDashboardController extends BaseApiController
{
    public function __construct(
        private readonly OwnerDashboardService $dashboard,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $payload = $this->dashboard->getSummary($request->user());

        return $this->successResponse($payload, 'Owner dashboard summary retrieved.');
    }
}
