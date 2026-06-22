<?php
/*
|--------------------------------------------------------------------------
| CityController — المدن (قراءة عامة)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\QueriesCities;
use App\Models\Cities;
use App\Traits\FormatsCityResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends BaseApiController
{
    use FormatsCityResponse, QueriesCities;

    public function index(Request $request): JsonResponse
    {
        $cities = $this->citiesQuery($request)
            ->withCount('places')
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $cities->through(fn (Cities $city) => $this->formatCity($city))->items(),
            'Cities retrieved.',
            200,
            $this->paginationMeta($cities),
        );
    }

    public function show(Cities $city): JsonResponse
    {
        $city->load(['places' => fn ($q) => $q->orderBy('name')])
            ->loadCount('places');

        return $this->successResponse(
            $this->formatCity($city),
            'City retrieved.',
        );
    }
}
