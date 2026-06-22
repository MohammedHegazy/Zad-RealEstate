<?php
/*
|--------------------------------------------------------------------------
| PlaceController — المناطق/الأحياء ضمن المدن
|--------------------------------------------------------------------------
|
| الغرض:
| - عرض المناطق للفلترة عند البحث عن عقارات أو شركات
| - عدّ العقارات النشطة والشركات المرتبطة بكل منطقة
|
| الارتباطات:
| - QueriesPlaces, FormatsPlaceResponse
| - Model Places → ينتمي إلى Cities
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\QueriesPlaces;
use App\Models\Cities;
use App\Models\Places;
use App\Traits\FormatsPlaceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends BaseApiController
{
    use FormatsPlaceResponse, QueriesPlaces;

    /**
     * قائمة المناطق مع إحصائيات.
     *
     * withCount:
     * - active_estates_count: عقارات status=active فقط
     * - companies: عدد الشركات في المنطقة
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->placesQuery($request)
            ->withCount([
                'estates as active_estates_count' => fn ($q) => $q->where('status', 'active'),
                'companies',
            ]);

        $places = $query->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $places->through(fn (Places $place) => $this->formatPlace($place))->items(),
            'Places retrieved.',
            200,
            $this->paginationMeta($places),
        );
    }

    /** تفاصيل منطقة واحدة */
    public function show(Places $place): JsonResponse
    {
        $place->load([
            'city:id,name,image',
        ])->loadCount([
            'estates as active_estates_count' => fn ($q) => $q->where('status', 'active'),
            'estates',
            'companies',
        ]);

        return $this->successResponse(
            $this->formatPlace($place),
            'Place retrieved.',
        );
    }

    /**
     * مناطق مدينة محددة — يعيد استخدام index بدمج cities_id في الطلب.
     *
     * المدخلات: Cities من الرابط
     * المخرجات: نفس استجابة index لكن مفلترة بالمدينة
     */
    public function indexByCity(Request $request, Cities $city): JsonResponse
    {
        $request->merge(['cities_id' => $city->id]);

        return $this->index($request);
    }
}
