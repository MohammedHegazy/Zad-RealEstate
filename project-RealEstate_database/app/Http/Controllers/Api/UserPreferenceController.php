<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserPreferenceRequest;
use App\Models\UserPreference;
use App\Traits\FormatsUserPreferenceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends BaseApiController
{
    use FormatsUserPreferenceResponse;

    /**
     * Get the authenticated user's recommendation preferences.
     */
    public function show(Request $request): JsonResponse
    {
        $preference = $request->user()
            ->preference()
            ->with(['city:id,name', 'place:id,name,cities_id'])
            ->first();

        return $this->successResponse(
            $preference ? $this->formatUserPreference($preference) : null,
            $preference === null
                ? 'No preferences saved yet. Create preferences to enable smart recommendations.'
                : 'User preferences retrieved.',
        );
    }

    /**
     * Create or update the user's preference profile (one per user).
     */
    public function store(StoreUserPreferenceRequest $request): JsonResponse
    {
        $preference = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return $this->successResponse(
            $this->formatUserPreference(
                $preference->fresh()->load(['city:id,name', 'place:id,name,cities_id'])
            ),
            'User preferences saved successfully.'
        );
    }

    /**
     * Remove preferences (recommendations will return empty until new preferences are saved).
     */
    public function destroy(Request $request): JsonResponse
    {
        $deleted = UserPreference::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        if ($deleted === 0) {
            return $this->notFoundResponse('No preferences found to delete.');
        }

        return $this->deletedResponse('User preferences deleted successfully.');
    }
}
