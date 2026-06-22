<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\ModerateReviewRequest;
use App\Http\Requests\Admin\ModerateVerificationRequestRequest;
use App\Http\Resources\Trust\AgentReviewResource;
use App\Http\Resources\Trust\CompanyReviewResource;
use App\Http\Resources\Trust\PropertyReviewResource;
use App\Http\Resources\Trust\TrustScoreResource;
use App\Http\Resources\Trust\VerificationRequestResource;
use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\PropertyReview;
use App\Models\VerificationRequest;
use App\Services\Trust\ReviewService;
use App\Services\Trust\TrustScoreService;
use App\Services\Trust\VerificationRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrustModerationController extends BaseApiController
{
    public function __construct(
        private readonly ReviewService $reviews,
        private readonly VerificationRequestService $verifications,
        private readonly TrustScoreService $trustScores,
    ) {}

    public function indexReviews(Request $request): JsonResponse
    {
        $this->authorize('moderate', PropertyReview::class);

        $type = $request->string('type')->toString() ?: 'property';

        try {
            $paginator = $this->reviews->adminListReviews(
                $type,
                $request->only(['status', 'search', 'user_id', 'estate_id', 'agent_id', 'company_id']),
                $request->integer('per_page', 15),
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->successResponse(
            $this->reviewResourceClass($type)::collection($paginator->items())->resolve(),
            'Reviews retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function pendingReviews(Request $request): JsonResponse
    {
        $request->merge(['status' => 'pending']);

        return $this->indexReviews($request);
    }

    public function showPropertyReview(PropertyReview $propertyReview): JsonResponse
    {
        $this->authorize('moderate', PropertyReview::class);

        $propertyReview->load(['user', 'estate', 'reviewer:id,username,fname,lname']);

        return $this->successResponse(
            (new PropertyReviewResource($propertyReview))->resolve(),
            'Property review retrieved.',
        );
    }

    public function showAgentReview(AgentReview $agentReview): JsonResponse
    {
        $this->authorize('moderate', AgentReview::class);

        $agentReview->load(['user', 'agent.user', 'reviewer:id,username,fname,lname']);

        return $this->successResponse(
            (new AgentReviewResource($agentReview))->resolve(),
            'Agent review retrieved.',
        );
    }

    public function showCompanyReview(CompanyReview $companyReview): JsonResponse
    {
        $this->authorize('moderate', CompanyReview::class);

        $companyReview->load(['user', 'company', 'reviewer:id,username,fname,lname']);

        return $this->successResponse(
            (new CompanyReviewResource($companyReview))->resolve(),
            'Company review retrieved.',
        );
    }

    public function indexVerifications(Request $request): JsonResponse
    {
        $this->authorize('moderate', VerificationRequest::class);

        $paginator = $this->verifications->adminList(
            $request->only(['status', 'search', 'user_id']),
            $request->integer('per_page', 15),
        );

        return $this->successResponse(
            VerificationRequestResource::collection($paginator->items())->resolve(),
            'Verification requests retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function pendingVerifications(Request $request): JsonResponse
    {
        $request->merge(['status' => 'pending']);

        return $this->indexVerifications($request);
    }

    public function showVerification(VerificationRequest $verificationRequest): JsonResponse
    {
        $this->authorize('moderate', VerificationRequest::class);

        $verificationRequest->load(['user', 'reviewer:id,username,fname,lname']);

        return $this->successResponse(
            (new VerificationRequestResource($verificationRequest))->resolve(),
            'Verification request retrieved.',
        );
    }

    public function downloadVerificationDocument(VerificationRequest $verificationRequest): StreamedResponse|JsonResponse
    {
        $this->authorize('moderate', VerificationRequest::class);

        $path = $this->verifications->documentAbsolutePath($verificationRequest);

        if (! $path) {
            return $this->notFoundResponse('Verification document not found.');
        }

        return Storage::disk('local')->download(
            $verificationRequest->document_path,
            $this->verifications->documentFilename($verificationRequest),
            ['Content-Type' => $this->verifications->documentMimeType($verificationRequest)],
        );
    }

    public function approvePropertyReview(ModerateReviewRequest $request, PropertyReview $propertyReview): JsonResponse
    {
        $this->authorize('moderate', PropertyReview::class);

        $review = $this->reviews->approveReview($propertyReview, $request->user());

        return $this->successResponse(
            (new PropertyReviewResource($review->load('user', 'estate', 'reviewer:id,username,fname,lname')))->resolve(),
            'Property review approved.',
        );
    }

    public function rejectPropertyReview(ModerateReviewRequest $request, PropertyReview $propertyReview): JsonResponse
    {
        $this->authorize('moderate', PropertyReview::class);

        $review = $this->reviews->rejectReview(
            $propertyReview,
            $request->validated('admin_notes'),
            $request->user(),
        );

        return $this->successResponse(
            (new PropertyReviewResource($review->load('user', 'estate', 'reviewer:id,username,fname,lname')))->resolve(),
            'Property review rejected.',
        );
    }

    public function deletePropertyReview(PropertyReview $propertyReview): JsonResponse
    {
        $this->authorize('moderate', PropertyReview::class);

        $this->reviews->adminDeleteReview($propertyReview);

        return $this->deletedResponse('Property review deleted successfully.');
    }

    public function approveAgentReview(ModerateReviewRequest $request, AgentReview $agentReview): JsonResponse
    {
        $this->authorize('moderate', AgentReview::class);

        $review = $this->reviews->approveReview($agentReview, $request->user());

        return $this->successResponse(
            (new AgentReviewResource($review->load('user', 'agent.user', 'reviewer:id,username,fname,lname')))->resolve(),
            'Agent review approved.',
        );
    }

    public function rejectAgentReview(ModerateReviewRequest $request, AgentReview $agentReview): JsonResponse
    {
        $this->authorize('moderate', AgentReview::class);

        $review = $this->reviews->rejectReview(
            $agentReview,
            $request->validated('admin_notes'),
            $request->user(),
        );

        return $this->successResponse(
            (new AgentReviewResource($review->load('user', 'agent.user', 'reviewer:id,username,fname,lname')))->resolve(),
            'Agent review rejected.',
        );
    }

    public function deleteAgentReview(AgentReview $agentReview): JsonResponse
    {
        $this->authorize('moderate', AgentReview::class);

        $this->reviews->adminDeleteReview($agentReview);

        return $this->deletedResponse('Agent review deleted successfully.');
    }

    public function approveCompanyReview(ModerateReviewRequest $request, CompanyReview $companyReview): JsonResponse
    {
        $this->authorize('moderate', CompanyReview::class);

        $review = $this->reviews->approveReview($companyReview, $request->user());

        return $this->successResponse(
            (new CompanyReviewResource($review->load('user', 'company', 'reviewer:id,username,fname,lname')))->resolve(),
            'Company review approved.',
        );
    }

    public function rejectCompanyReview(ModerateReviewRequest $request, CompanyReview $companyReview): JsonResponse
    {
        $this->authorize('moderate', CompanyReview::class);

        $review = $this->reviews->rejectReview(
            $companyReview,
            $request->validated('admin_notes'),
            $request->user(),
        );

        return $this->successResponse(
            (new CompanyReviewResource($review->load('user', 'company', 'reviewer:id,username,fname,lname')))->resolve(),
            'Company review rejected.',
        );
    }

    public function deleteCompanyReview(CompanyReview $companyReview): JsonResponse
    {
        $this->authorize('moderate', CompanyReview::class);

        $this->reviews->adminDeleteReview($companyReview);

        return $this->deletedResponse('Company review deleted successfully.');
    }

    public function approveVerification(ModerateVerificationRequestRequest $request, VerificationRequest $verificationRequest): JsonResponse
    {
        $this->authorize('moderate', VerificationRequest::class);

        $verification = $this->verifications->approve(
            $verificationRequest,
            $request->user(),
            $request->validated('admin_notes'),
        );

        return $this->successResponse(
            (new VerificationRequestResource($verification->load(['user', 'reviewer:id,username,fname,lname'])))->resolve(),
            'Verification request approved.',
        );
    }

    public function rejectVerification(ModerateVerificationRequestRequest $request, VerificationRequest $verificationRequest): JsonResponse
    {
        $this->authorize('moderate', VerificationRequest::class);

        $verification = $this->verifications->reject(
            $verificationRequest,
            $request->user(),
            $request->validated('admin_notes'),
        );

        return $this->successResponse(
            (new VerificationRequestResource($verification->load(['user', 'reviewer:id,username,fname,lname'])))->resolve(),
            'Verification request rejected.',
        );
    }

    public function recalculateAgentTrust(Agent $agent): JsonResponse
    {
        $this->authorize('moderate', AgentReview::class);

        $score = $this->trustScores->recalculateForAgent($agent);

        return $this->successResponse(
            (new TrustScoreResource($score))->resolve(),
            'Agent trust score recalculated.',
        );
    }

    public function recalculateCompanyTrust(Companies $company): JsonResponse
    {
        $this->authorize('moderate', CompanyReview::class);

        $score = $this->trustScores->recalculateForCompany($company);

        return $this->successResponse(
            (new TrustScoreResource($score))->resolve(),
            'Company trust score recalculated.',
        );
    }

    /**
     * @return class-string<PropertyReviewResource|AgentReviewResource|CompanyReviewResource>
     */
    private function reviewResourceClass(string $type): string
    {
        return match ($type) {
            'agent' => AgentReviewResource::class,
            'company' => CompanyReviewResource::class,
            default => PropertyReviewResource::class,
        };
    }
}
