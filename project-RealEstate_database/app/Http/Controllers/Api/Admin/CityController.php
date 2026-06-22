<?php
/*
|--------------------------------------------------------------------------
| CityController — إدارة المدن (CRUD للمدير)
|--------------------------------------------------------------------------
| المدينة تحتوي أحياء/مناطق (places). لا تُحذف مدينة فيها مناطق مرتبطة.
| الصورة تُرفع عبر ManagesCityImages ويُعاد رابطها العام في الرد عبر Trait.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\ManagesCityImages;
use App\Http\Controllers\Concerns\QueriesCities;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Models\Cities;
use App\Traits\FormatsCityResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends BaseApiController
{
    use FormatsCityResponse, ManagesCityImages, QueriesCities;

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

    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = Cities::create($this->cityDataFromRequest($request));

        return $this->createdResponse(
            $this->formatCity($city),
            'City created successfully.',
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

    public function update(UpdateCityRequest $request, Cities $city): JsonResponse
    {
        $city->update($this->cityDataFromRequest($request, $city));

        return $this->successResponse(
            $this->formatCity($city->fresh()),
            'City updated successfully.',
        );
    }

    public function destroy(Cities $city): JsonResponse
    {
        if ($city->places()->exists()) {
            return $this->errorResponse('Cannot delete a city that has places.', 422);
        }

        $this->deleteCityImage($city);
        $city->delete();

        return $this->deletedResponse('City deleted successfully.');
    }
}
