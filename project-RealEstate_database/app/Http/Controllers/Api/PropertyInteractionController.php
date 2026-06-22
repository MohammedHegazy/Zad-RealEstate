<?php

namespace App\Http\Controllers\Api;

use App\Enums\InteractionType;
use App\Http\Requests\StorePropertyInteractionRequest;
use App\Models\Estate;
use App\Models\PropertyInteraction;
use App\Services\PropertyInteractionService;
use App\Traits\FormatsPropertyInteractionResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyInteractionController extends BaseApiController
{
    use FormatsPropertyInteractionResponse;

    public function __construct(
        private readonly PropertyInteractionService $interactions,
    ) {}

    /**
     * Interaction history for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->propertyInteractions()
            ->with('estate:id,name,price,type_text,kind_text,places_id,status');

        if ($request->filled('estate_id')) {
            $query->where('estate_id', $request->integer('estate_id'));
        }

        if ($request->filled('interaction_type')) {
            $query->where('interaction_type', $request->interaction_type);
        }

        $records = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->successResponse(
            $records->through(fn (PropertyInteraction $i) => $this->formatPropertyInteraction($i)),
            'Property interactions retrieved.',
            200,
            $this->paginationMeta($records)
        );
    }

    /**
     * Behavioral profile inferred from property_interactions (for debugging / UI).
     */
    public function insights(Request $request): JsonResponse
    {
        $profile = $this->interactions->inferBehavioralProfile($request->user());

        return $this->successResponse(
            [
                'behavioral_profile' => $profile,
                'score_weights' => config('realestate.interaction_scores'),
            ],
            $profile
                ? 'Behavioral profile inferred from your activity.'
                : 'No property interactions recorded yet.'
        );
    }

    /**
     * Manually log an interaction (e.g. contact_agent).
     */
    public function store(StorePropertyInteractionRequest $request): JsonResponse
    {
        $estate = Estate::findOrFail($request->integer('estate_id'));

        return $this->recordForEstate($request, $estate, $request->enum('interaction_type', InteractionType::class));
    }

    public function storeForEstate(
        StorePropertyInteractionRequest $request,
        Estate $estate,
    ): JsonResponse {
        if ($estate->status !== 'active') {
            return $this->errorResponse('Only active estates accept interactions.', 422);
        }

        $type = $request->enum('interaction_type', InteractionType::class)
            ?? InteractionType::ContactAgent;

        return $this->recordForEstate($request, $estate, $type);
    }

    /**
     * Log contact-agent intent for a listing.
     */
    public function contactAgent(Request $request, Estate $estate): JsonResponse
    {
        if ($estate->status !== 'active') {
            return $this->notFoundResponse('Estate not found.');
        }

        $interaction = $this->interactions->record(
            $request->user(),
            $estate,
            InteractionType::ContactAgent
        );

        $estate->load('user:id,username,fname,lname,phone,email');

        return $this->successResponse(
            [
                'interaction' => $this->formatPropertyInteraction(
                    $interaction->load('estate:id,name,price')
                ),
                'listing_owner' => $estate->user?->only([
                    'id', 'username', 'fname', 'lname', 'phone', 'email',
                ]),
            ],
            'Contact request recorded. Listing owner details returned.'
        );
    }

    private function recordForEstate(
        Request $request,
        Estate $estate,
        InteractionType $type,
    ): JsonResponse {
        if ($estate->status !== 'active') {
            return $this->errorResponse('Only active estates accept interactions.', 422);
        }

        $interaction = $this->interactions->record(
            $request->user(),
            $estate,
            $type,
            $request->integer('interaction_score') ?: null
        );

        return $this->createdResponse(
            $this->formatPropertyInteraction($interaction->load('estate:id,name,price')),
            'Property interaction recorded.'
        );
    }
}
