<?php
/*
|--------------------------------------------------------------------------
| شرح تعليمي للمشروع
|--------------------------------------------------------------------------
| هذا الملف جزء من مشروع منصة عقارية ذكية مبني باستخدام Laravel.
|
| الهدف من هذا الملف:
| - شرح كيفية عمل النظام
| - توضيح Architecture
| - فهم العلاقة بين Controller و Service و Model
|
| ملاحظات:
| - Controller يستقبل الطلبات
| - Service يحتوي Business Logic
| - Model يتعامل مع قاعدة البيانات
|--------------------------------------------------------------------------
*/


namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreFavoriteAgentRequest;
use App\Models\Agent;
use App\Models\Favorit_agent;
use App\Traits\FormatsAgentResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteAgentController extends BaseApiController
{
    use FormatsAgentResponse;

    /**
     * List the authenticated user's favorite agents (favorite_agents pivot).
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->favoriteAgents()
            ->with([
                'agent' => fn ($q) => $q
                    ->with([
                        'user:id,username,fname,lname,email,phone,country_code_phone,type',
                        'company:id,company_name,places_id',
                    ])
                    ->withAvg('approvedReviews', 'rating')
                    ->withCount('approvedReviews'),
            ]);

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->integer('agent_id'));
        }

        if ($request->filled('companies_id')) {
            $query->whereHas('agent', fn ($q) => $q->where('companies_id', $request->companies_id));
        }

        $favorites = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $favorites->through(fn (Favorit_agent $f) => $this->formatFavorite($f)),
            'Favorite agents retrieved.',
            200,
            $this->paginationMeta($favorites)
        );
    }

    /**
     * Add an agent to favorites (POST body: agent_id).
     */
    public function store(StoreFavoriteAgentRequest $request): JsonResponse
    {
        $agent = Agent::findOrFail($request->validated('agent_id'));

        if ($this->isOwnAgent($request, $agent)) {
            return $this->errorResponse('You cannot favorite your own agent profile.', 422);
        }

        $favorite = $this->addFavorite($request->user()->id, $agent->id);

        return $this->createdResponse(
            $this->formatFavorite($favorite),
            'Agent added to favorites.'
        );
    }

    /**
     * Add favorite via agent route parameter (POST /agents/{agent}/favorite).
     */
    public function storeByAgent(Request $request, Agent $agent): JsonResponse
    {
        if ($this->isOwnAgent($request, $agent)) {
            return $this->errorResponse('You cannot favorite your own agent profile.', 422);
        }

        $favorite = $this->addFavorite($request->user()->id, $agent->id);

        return $this->createdResponse(
            $this->formatFavorite($favorite),
            'Agent added to favorites.'
        );
    }

    public function show(Request $request, Favorit_agent $favoriteAgent): JsonResponse
    {
        if (! $this->ownsFavorite($request, $favoriteAgent)) {
            return $this->notFoundResponse('Favorite agent not found.');
        }

        $favoriteAgent->load([
            'agent' => fn ($q) => $q
                ->with([
                    'user:id,username,fname,lname,email,phone,country_code_phone,type',
                    'company:id,company_name,places_id',
                ])
                ->withAvg('approvedReviews', 'rating')
                ->withCount('approvedReviews'),
        ]);

        return $this->successResponse(
            $this->formatFavorite($favoriteAgent),
            'Favorite agent retrieved.'
        );
    }

    /**
     * Remove a favorite by pivot record id.
     */
    public function destroy(Request $request, Favorit_agent $favoriteAgent): JsonResponse
    {
        if (! $this->ownsFavorite($request, $favoriteAgent)) {
            return $this->notFoundResponse('Favorite agent not found.');
        }

        $favoriteAgent->delete();

        return $this->deletedResponse('Agent removed from favorites.');
    }

    /**
     * Remove a favorite by agent id (DELETE /agents/{agent}/favorite).
     */
    public function destroyByAgent(Request $request, Agent $agent): JsonResponse
    {
        $deleted = Favorit_agent::query()
            ->where('user_id', $request->user()->id)
            ->where('agent_id', $agent->id)
            ->delete();

        if ($deleted === 0) {
            return $this->notFoundResponse('Agent is not in your favorites.');
        }

        return $this->deletedResponse('Agent removed from favorites.');
    }

    /**
     * Check whether the authenticated user favorited a given agent.
     */
    public function check(Request $request, Agent $agent): JsonResponse
    {
        $favorited = Favorit_agent::query()
            ->where('user_id', $request->user()->id)
            ->where('agent_id', $agent->id)
            ->exists();

        return $this->successResponse([
            'agent_id' => $agent->id,
            'favorited' => $favorited,
        ], 'Favorite status retrieved.');
    }

    private function addFavorite(int $userId, int $agentId): Favorit_agent
    {
        $favorite = Favorit_agent::firstOrCreate([
            'user_id' => $userId,
            'agent_id' => $agentId,
        ]);

        $favorite->load([
            'agent.user:id,username,fname,lname,email,phone,country_code_phone,type',
            'agent.company:id,company_name,places_id',
        ]);
        $favorite->agent?->loadAvg('approvedReviews', 'rating')->loadCount('approvedReviews');

        return $favorite;
    }

    private function formatFavorite(Favorit_agent $favorite): array
    {
        $data = $favorite->only(['id', 'user_id', 'agent_id', 'created_at', 'updated_at']);

        if ($favorite->relationLoaded('agent') && $favorite->agent) {
            $data['agent'] = $this->formatAgent($favorite->agent);
        }

        return $data;
    }

    private function ownsFavorite(Request $request, Favorit_agent $favorite): bool
    {
        return $favorite->user_id === $request->user()->id;
    }

    private function isOwnAgent(Request $request, Agent $agent): bool
    {
        return $agent->user_id === $request->user()->id;
    }
}
