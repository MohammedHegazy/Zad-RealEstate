<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Admin\StatisticsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseApiController
{
    public function __construct(
        private readonly StatisticsService $statistics,
    ) {}

    public function statistics(): JsonResponse
    {
        return $this->successResponse(
            $this->statistics->overview(),
            'Dashboard statistics retrieved.',
        );
    }
}
