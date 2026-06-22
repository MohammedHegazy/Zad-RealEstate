<?php

namespace App\Http\Controllers\Api\Trust;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Trust\StorePropertyReviewRequest;
use App\Http\Requests\Trust\UpdatePropertyReviewRequest;
use App\Http\Resources\Trust\PropertyReviewResource;
use App\Http\Resources\Trust\RatingSummaryResource;
use App\Models\Estate;
use App\Models\PropertyReview;
use App\Services\Trust\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PropertyReviewController extends BaseApiController
{
    public function __construct(
        private readonly ReviewService $reviews,
    ) {}

    public function index(Request $request, Estate $estate): JsonResponse
    {
        $paginator = $this->reviews->listForSubject(
            PropertyReview::class,
            $estate->id,
            'estate_id',
            $request->integer('per_page', 15),
        );

        return $this->successResponse(
            PropertyReviewResource::collection($paginator->items())->resolve(),
            'Property reviews retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function summary(Estate $estate): JsonResponse
    {
        $summary = $this->reviews->ratingSummary(PropertyReview::class, $estate->id, 'estate_id');

        return $this->successResponse(
            (new RatingSummaryResource($summary))->resolve(),
            'Property rating summary retrieved.',
        );
    }

    public function myReviewForEstate(Request $request, Estate $estate): JsonResponse
    {
        $review = PropertyReview::query()
            ->where('user_id', $request->user()->id)
            ->where('estate_id', $estate->id)
            ->first();

        return $this->successResponse([
            'estate_id' => $estate->id,
            'has_reviewed' => $review !== null,
            'review' => $review ? (new PropertyReviewResource($review))->resolve() : null,
        ], 'Your review for this property retrieved.');
    }

    public function store(StorePropertyReviewRequest $request, Estate $estate): JsonResponse
    {
        $this->authorize('create', PropertyReview::class);

        try {
            $review = $this->reviews->createPropertyReview(
                $request->user(),
                $estate,
                $request->validated(),
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        $review->load('user:id,username,fname,lname');

        return $this->createdResponse(
            (new PropertyReviewResource($review))->resolve(),
            'Property review submitted and pending approval.',
        );
    }

    public function update(UpdatePropertyReviewRequest $request, PropertyReview $propertyReview): JsonResponse
    {
        $this->authorize('update', $propertyReview);

        $review = $this->reviews->updateReview($propertyReview, $request->validated());
        $review->load('user:id,username,fname,lname');

        return $this->successResponse(
            (new PropertyReviewResource($review))->resolve(),
            'Property review updated and pending approval.',
        );
    }

    public function destroy(PropertyReview $propertyReview): JsonResponse
    {
        $this->authorize('delete', $propertyReview);

        $this->reviews->deleteReview($propertyReview);

        return $this->deletedResponse('Property review deleted.');
    }
}
