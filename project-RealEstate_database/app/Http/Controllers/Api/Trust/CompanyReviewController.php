<?php

namespace App\Http\Controllers\Api\Trust;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Trust\StoreCompanyReviewRequest;
use App\Http\Requests\Trust\UpdateCompanyReviewRequest;
use App\Http\Resources\Trust\CompanyReviewResource;
use App\Http\Resources\Trust\RatingSummaryResource;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Services\Trust\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CompanyReviewController extends BaseApiController
{
    public function __construct(
        private readonly ReviewService $reviews,
    ) {}

    public function index(Request $request, Companies $company): JsonResponse
    {
        $paginator = $this->reviews->listForSubject(
            CompanyReview::class,
            $company->id,
            'company_id',
            $request->integer('per_page', 15),
        );

        return $this->successResponse(
            CompanyReviewResource::collection($paginator->items())->resolve(),
            'Company reviews retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function summary(Companies $company): JsonResponse
    {
        $summary = $this->reviews->ratingSummary(CompanyReview::class, $company->id, 'company_id');

        return $this->successResponse(
            (new RatingSummaryResource($summary))->resolve(),
            'Company rating summary retrieved.',
        );
    }

    public function myReviewForCompany(Request $request, Companies $company): JsonResponse
    {
        $review = CompanyReview::query()
            ->where('user_id', $request->user()->id)
            ->where('company_id', $company->id)
            ->first();

        return $this->successResponse([
            'company_id' => $company->id,
            'has_reviewed' => $review !== null,
            'review' => $review ? (new CompanyReviewResource($review))->resolve() : null,
        ], 'Your review for this company retrieved.');
    }

    public function store(StoreCompanyReviewRequest $request, Companies $company): JsonResponse
    {
        $this->authorize('create', CompanyReview::class);

        try {
            $review = $this->reviews->createCompanyReview(
                $request->user(),
                $company,
                $request->validated(),
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        $review->load('user:id,username,fname,lname');

        return $this->createdResponse(
            (new CompanyReviewResource($review))->resolve(),
            'Company review submitted and pending approval.',
        );
    }

    public function update(UpdateCompanyReviewRequest $request, CompanyReview $companyReview): JsonResponse
    {
        $this->authorize('update', $companyReview);

        $review = $this->reviews->updateReview($companyReview, $request->validated());
        $review->load('user:id,username,fname,lname');

        return $this->successResponse(
            (new CompanyReviewResource($review))->resolve(),
            'Company review updated and pending approval.',
        );
    }

    public function destroy(CompanyReview $companyReview): JsonResponse
    {
        $this->authorize('delete', $companyReview);

        $this->reviews->deleteReview($companyReview);

        return $this->deletedResponse('Company review deleted.');
    }
}
