<?php


namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreFavoriteEstateRequest;
use App\Models\Estate;
use App\Models\Favorit_estate;
use App\Services\RecommendationGeneratorService;
use App\Traits\FormatsEstateResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteEstateController extends BaseApiController
{
    use FormatsEstateResponse;

    public function __construct(
        private readonly RecommendationGeneratorService $recommendations,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->favoriteEstates()
            ->with([
                'estate.place.city',
                'estate.images',
                'estate.user:id,username,fname,lname,country_code_phone',
            ]);

        if ($request->boolean('active_only')) {
            $query->whereHas('estate', fn ($q) => $q->where('status', 'active'));
        }

        if ($request->filled('estate_id')) {
            $query->where('estate_id', $request->integer('estate_id'));
        }

        $favorites = $query->latest()->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $favorites->through(fn (Favorit_estate $f) => $this->formatFavorite($f))->items(),
            'Favorite estates retrieved.',
            200,
            $this->paginationMeta($favorites),
        );
    }

    public function store(StoreFavoriteEstateRequest $request): JsonResponse
    {
        $favorite = $this->addFavorite($request->user()->id, (int) $request->validated('estate_id'));
        $this->recommendations->generateForUser($request->user());

        return $this->createdResponse(
            $this->formatFavorite($favorite),
            'Estate added to favorites.',
        );
    }

    public function storeByEstate(Request $request, Estate $estate): JsonResponse
    {
        if ($estate->status !== 'active') {
            return $this->errorResponse('Only active estates can be favorited.', 422);
        }

        $favorite = $this->addFavorite($request->user()->id, $estate->id);
        $this->recommendations->generateForUser($request->user());

        return $this->createdResponse(
            $this->formatFavorite($favorite),
            'Estate added to favorites.',
        );
    }

    public function show(Request $request, Favorit_estate $favoriteEstate): JsonResponse
    {
        if (! $this->ownsFavorite($request, $favoriteEstate)) {
            return $this->notFoundResponse('Favorite estate not found.');
        }

        $favoriteEstate->load([
            'estate.place.city',
            'estate.images',
            'estate.user:id,username,fname,lname,country_code_phone',
        ]);

        return $this->successResponse(
            $this->formatFavorite($favoriteEstate),
            'Favorite estate retrieved.',
        );
    }

    public function destroy(Request $request, Favorit_estate $favoriteEstate): JsonResponse
    {
        if (! $this->ownsFavorite($request, $favoriteEstate)) {
            return $this->notFoundResponse('Favorite estate not found.');
        }

        $favoriteEstate->delete();
        $this->recommendations->generateForUser($request->user());

        return $this->deletedResponse('Estate removed from favorites.');
    }

    public function destroyByEstate(Request $request, Estate $estate): JsonResponse
    {
        $deleted = Favorit_estate::query()
            ->where('user_id', $request->user()->id)
            ->where('estate_id', $estate->id)
            ->delete();

        if ($deleted === 0) {
            return $this->notFoundResponse('Estate is not in your favorites.');
        }

        $this->recommendations->generateForUser($request->user());

        return $this->deletedResponse('Estate removed from favorites.');
    }

    public function check(Request $request, Estate $estate): JsonResponse
    {
        $favorited = Favorit_estate::query()
            ->where('user_id', $request->user()->id)
            ->where('estate_id', $estate->id)
            ->exists();

        return $this->successResponse(
            [
                'estate_id' => $estate->id,
                'favorited' => $favorited,
            ],
            'Favorite status retrieved.',
        );
    }

    private function addFavorite(int $userId, int $estateId): Favorit_estate
    {
        $favorite = Favorit_estate::firstOrCreate([
            'user_id' => $userId,
            'estate_id' => $estateId,
        ]);

        return $favorite->load([
            'estate.place.city',
            'estate.images',
            'estate.user:id,username,fname,lname,country_code_phone',
        ]);
    }

    private function formatFavorite(Favorit_estate $favorite): array
    {
        $data = $favorite->only(['id', 'user_id', 'estate_id', 'created_at', 'updated_at']);

        if ($favorite->relationLoaded('estate') && $favorite->estate) {
            $data['estate'] = $this->formatEstate($favorite->estate);
        }

        return $data;
    }

    private function ownsFavorite(Request $request, Favorit_estate $favorite): bool
    {
        return $favorite->user_id === $request->user()->id;
    }
}
