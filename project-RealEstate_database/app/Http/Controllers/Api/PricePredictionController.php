<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePricePredictionPreviewRequest;
use App\Models\Estate;
use App\Services\Ai\EstatePricePredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PricePredictionController extends BaseApiController
{
    public function __construct(
        private readonly EstatePricePredictionService $predictions,
    ) {}

    /**
     * ML price prediction for an existing listing (replaces direct Vue → Flask calls).
     */
    public function forEstate(Request $request, Estate $estate): JsonResponse
    {
        if (! $this->canViewEstate($request, $estate)) {
            return $this->notFoundResponse('Estate not found.');
        }

        try {
            $data = $this->predictions->predictForEstate($estate, $request->user());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 503);
        }

        return $this->successResponse($data, 'Price prediction generated.');
    }

    /**
     * Ad-hoc prediction while creating or editing a listing.
     * يقوم بتوقع السعر بناءً على البيانات المقدمة من المستخدم.
     * ويتم استخدامه لتوقع السعر قبل حفظ العقار.
     * StorePricePredictionPreviewRequest 11. التحقق من البيانات اذاموجودة او لا 
     */
    public function preview(StorePricePredictionPreviewRequest $request): JsonResponse
    {
        try {
            $data = $this->predictions->predictFromInput(
                $request->validated(),
                $request->user()
            );
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 503);
        }

        return $this->successResponse($data, 'Price prediction generated.');
    }

    private function canViewEstate(Request $request, Estate $estate): bool
    {
        if ($estate->status === 'active') {
            return true;
        }

        return $request->user() && $estate->user_id === $request->user()->id;
    }
}
