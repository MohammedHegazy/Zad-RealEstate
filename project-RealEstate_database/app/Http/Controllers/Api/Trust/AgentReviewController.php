<?php

namespace App\Http\Controllers\Api\Trust;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Trust\StoreAgentReviewRequest;
use App\Http\Requests\Trust\UpdateAgentReviewRequest;
use App\Http\Resources\Trust\AgentReviewResource;
use App\Http\Resources\Trust\RatingSummaryResource;
use App\Models\Agent;
use App\Models\AgentReview;
use App\Services\Trust\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AgentReviewController extends BaseApiController
{
    public function __construct(
        private readonly ReviewService $reviews,
    ) {}

    public function index(Request $request, Agent $agent): JsonResponse
    {
        $paginator = $this->reviews->listForSubject(
            AgentReview::class,
            $agent->id,
            'agent_id',
            $request->integer('per_page', 15),
        );

        return $this->successResponse(
            AgentReviewResource::collection($paginator->items())->resolve(),
            'Agent reviews retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function summary(Agent $agent): JsonResponse
    {
        $summary = $this->reviews->ratingSummary(AgentReview::class, $agent->id, 'agent_id');

        return $this->successResponse(
            (new RatingSummaryResource($summary))->resolve(),
            'Agent rating summary retrieved.',
        );
    }

    public function indexMine(Request $request): JsonResponse
    {
        $paginator = $request->user()
            ->agentReviews()
            ->with(['agent.user:id,username,fname,lname', 'agent.company:id,company_name'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            AgentReviewResource::collection($paginator->items())->resolve(),
            'Your agent reviews retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function myReviewForAgent(Request $request, Agent $agent): JsonResponse
    {
        $review = AgentReview::query()
            ->where('user_id', $request->user()->id)
            ->where('agent_id', $agent->id)
            ->first();

        return $this->successResponse([
            'agent_id' => $agent->id,
            'has_reviewed' => $review !== null,
            'review' => $review ? (new AgentReviewResource($review))->resolve() : null,
        ], 'Your review for this agent retrieved.');
    }

    public function store(StoreAgentReviewRequest $request, Agent $agent): JsonResponse
    {
        $this->authorize('create', AgentReview::class);

        try {
            $review = $this->reviews->createAgentReview(
                $request->user(),
                $agent,
                $request->validated(),
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        $review->load('user:id,username,fname,lname');

        return $this->createdResponse(
            (new AgentReviewResource($review))->resolve(),
            'Agent review submitted and pending approval.',
        );
    }

    public function update(UpdateAgentReviewRequest $request, AgentReview $agentReview): JsonResponse
    {
        $this->authorize('update', $agentReview);

        $review = $this->reviews->updateReview($agentReview, $request->validated());
        $review->load('user:id,username,fname,lname');

        return $this->successResponse(
            (new AgentReviewResource($review))->resolve(),
            'Agent review updated and pending approval.',
        );
    }

    public function destroy(AgentReview $agentReview): JsonResponse
    {
        $this->authorize('delete', $agentReview);

        $this->reviews->deleteReview($agentReview);

        return $this->deletedResponse('Agent review deleted.');
    }
}
