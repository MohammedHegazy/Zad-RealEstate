<?php
/*
|--------------------------------------------------------------------------
| EstateAdController — صور الإعلان الترويجية للعقار
|--------------------------------------------------------------------------
|
| الغرض:
| - إدارة جدول estate_ads (صور تسويقية، is_main للصورة البارزة)
|
| الفرق عن EstateImageController:
| - صور الإعلان قد تُستخدم في بطاقات مختلفة عن معرض العقار
|
| الارتباطات:
| - ManagesEstateAds
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ManagesEstateAds;
use App\Http\Requests\StoreEstateAdRequest;
use App\Models\Estate;
use App\Models\EstateAd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstateAdController extends BaseApiController
{
    use ManagesEstateAds;

    /**
     * عرض إعلانات العقار — المالك أو من يملك صلاحية canManageAds (هنا = المالك).
     */
    public function index(Request $request, Estate $estate): JsonResponse
    {
        if (! $this->canManageAds($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $ads = $estate->ads()->latest()->get()->map(fn (EstateAd $ad) => $this->formatEstateAd($ad));

        return $this->successResponse($ads, 'Estate ads retrieved.');
    }

    /** رفع صورة إعلان جديدة */
    public function store(StoreEstateAdRequest $request, Estate $estate): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        $ad = $this->createEstateAd(
            $estate,
            $request->file('image'),
            $request->boolean('is_main')
        );

        return $this->createdResponse(
            $this->formatEstateAd($ad),
            'Ad image uploaded successfully.',
        );
    }

    /** تعيين إعلان كصورة رئيسية (is_main) */
    public function setMain(Request $request, Estate $estate, EstateAd $ad): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        if ($ad->estate_id !== $estate->id) {
            return $this->notFoundResponse('Ad does not belong to this estate.');
        }

        $estate->ads()->update(['is_main' => false]);
        $ad->update(['is_main' => true]);

        return $this->successResponse(
            $this->formatEstateAd($ad->fresh()),
            'Main ad image updated.',
        );
    }

    public function destroy(Request $request, Estate $estate, EstateAd $ad): JsonResponse
    {
        if (! $this->isOwner($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        if ($ad->estate_id !== $estate->id) {
            return $this->notFoundResponse('Ad does not belong to this estate.');
        }

        $this->deleteEstateAdFile($ad);
        $ad->delete();

        return $this->deletedResponse('Ad image deleted successfully.');
    }

    private function isOwner(Request $request, Estate $estate): bool
    {
        return $estate->user_id === $request->user()->id;
    }

    private function canManageAds(Request $request, Estate $estate): bool
    {
        return $this->isOwner($request, $estate);
    }
}
