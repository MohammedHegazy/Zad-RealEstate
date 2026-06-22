<?php
/*
|--------------------------------------------------------------------------
| PlaceController — إدارة المناطق/الأحياء داخل المدن (CRUD للمدير)
|--------------------------------------------------------------------------
| كل place تنتمي لمدينة (cities_id). الحذف ممنوع إن وُجدت عقارات أو شركات.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\QueriesPlaces;
use App\Http\Requests\Admin\StorePlaceRequest;
use App\Http\Requests\Admin\UpdatePlaceRequest;
use App\Models\Places;
use App\Traits\FormatsPlaceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends BaseApiController
{
    use FormatsPlaceResponse, QueriesPlaces;

    public function index(Request $request): JsonResponse
    {
        $places = $this->placesQuery($request)
            ->withCount(['estates', 'companies'])
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $places->through(fn (Places $place) => $this->formatPlace($place))->items(),
            'Places retrieved.',
            200,
            $this->paginationMeta($places),
        );
    }

    public function store(StorePlaceRequest $request): JsonResponse
    {
        $place = Places::create($request->validated());

        return $this->createdResponse(
            $this->formatPlace($place->load('city:id,name,image')),
            'Place created successfully.',
        );
    }

    public function show(Places $place): JsonResponse
    {
        $place->load([
            'city:id,name,image',
            'estates:id,places_id,name,status,price',
            'companies:id,places_id,company_name',
        ])->loadCount(['estates', 'companies']);

        return $this->successResponse(
            $this->formatPlace($place),
            'Place retrieved.',
        );
    }

    public function update(UpdatePlaceRequest $request, Places $place): JsonResponse
    {
        $place->update($request->validated());

        return $this->successResponse(
            $this->formatPlace($place->fresh()->load('city:id,name,image')),
            'Place updated successfully.',
        );
    }

    public function destroy(Places $place): JsonResponse
    {
        if ($place->estates()->exists() || $place->companies()->exists()) {
            return $this->errorResponse('Cannot delete a place linked to estates or companies.', 422);
        }

        $place->delete();

        return $this->deletedResponse('Place deleted successfully.');
    }
}
