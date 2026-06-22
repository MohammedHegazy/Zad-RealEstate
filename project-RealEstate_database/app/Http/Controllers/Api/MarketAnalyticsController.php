<?php

namespace App\Http\Controllers\Api;

use App\Services\Ai\MarketTrendsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketAnalyticsController extends BaseApiController
{
    public function __construct(
        private readonly MarketTrendsService $trends,
    ) {}

    /**
     * Listing and prediction aggregates for dashboards (SQL-based trends).
     */
    public function marketTrends(Request $request): JsonResponse
    {
        $placesId = $request->filled('places_id') ? $request->integer('places_id') : null;
        $citiesId = $request->filled('cities_id') ? $request->integer('cities_id') : null;

        return $this->successResponse(
            $this->trends->summarize($placesId, $citiesId),
            'Market trends retrieved.'
        );
    }
}
