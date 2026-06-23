<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\ManagesEstateAds;
use App\Http\Requests\StoreEstateAdRequest;
use App\Models\Estate;
use App\Models\EstateAd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstateAdController extends BaseApiController
{
    use ManagesEstateAds;

    public function index(Estate $estate): JsonResponse
    {
        $ads = $estate->ads()->orderBy('sort_order')->orderBy('id')->get()->map(fn (EstateAd $ad) => $this->formatEstateAd($ad));

        return $this->successResponse($ads, 'Estate ads retrieved.');
    }

    public function reorder(Request $request, Estate $estate): JsonResponse
    {
        $request->validate([
            'ad_ids' => ['required', 'array'],
            'ad_ids.*' => ['integer', 'exists:estate_ads,id'],
        ]);

        foreach ($request->input('ad_ids') as $index => $id) {
            $estate->ads()->where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return $this->successResponse(null, 'Ads reordered successfully.');
    }

    public function store(StoreEstateAdRequest $request, Estate $estate): JsonResponse
    {
        $ad = $this->createEstateAd($estate, $request->file('image'), $request->boolean('is_main'));

        return $this->createdResponse(
            $this->formatEstateAd($ad),
            'Ad image uploaded successfully.',
        );
    }

    public function setMain(Request $request, Estate $estate, EstateAd $ad): JsonResponse
    {
        if ($ad->estate_id !== $estate->id) {
            return $this->notFoundResponse('Ad does not belong to this estate.');
        }

        $estate->ads()->update(['is_main' => false]);
        $ad->update(['is_main' => true]);

        return $this->successResponse(
            $this->formatEstateAd($ad->fresh()),
            'Main ad updated.',
        );
    }

    public function destroy(Estate $estate, EstateAd $ad): JsonResponse
    {
        if ($ad->estate_id !== $estate->id) {
            return $this->notFoundResponse('Ad does not belong to this estate.');
        }

        $this->deleteEstateAdFile($ad);
        $ad->delete();

        return $this->deletedResponse('Ad deleted successfully.');
    }
}
