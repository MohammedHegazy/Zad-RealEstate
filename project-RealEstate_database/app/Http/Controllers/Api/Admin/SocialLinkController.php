<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreSocialLinkRequest;
use App\Http\Requests\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialLinkController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = SocialLink::query()->with('socialable');

        if ($request->filled('socialable_type')) {
            $query->where('socialable_type', $request->socialable_type);
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('url', 'like', "%{$search}%");
        }

        $links = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->successResponse(
            $links->items(),
            'Social links retrieved.',
            200,
            $this->paginationMeta($links),
        );
    }

    public function store(StoreSocialLinkRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'socialable_type' => ['required', 'string'],
            'socialable_id' => ['required', 'integer', 'min:1'],
        ]);

        $data = $request->validated();

        $exists = SocialLink::query()
            ->where('socialable_type', $validated['socialable_type'])
            ->where('socialable_id', $validated['socialable_id'])
            ->where('platform', $data['platform'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('This platform already exists for the target entity.', 422);
        }

        $link = SocialLink::create([
            ...$data,
            ...$validated,
        ]);

        return $this->createdResponse($link, 'Social link created.');
    }

    public function show(SocialLink $socialLink): JsonResponse
    {
        return $this->successResponse(
            $socialLink->load('socialable'),
            'Social link retrieved.',
        );
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): JsonResponse
    {
        if ($request->filled('platform') && $request->platform !== $socialLink->platform) {
            $duplicate = SocialLink::query()
                ->where('socialable_type', $socialLink->socialable_type)
                ->where('socialable_id', $socialLink->socialable_id)
                ->where('platform', $request->platform)
                ->whereKeyNot($socialLink->id)
                ->exists();

            if ($duplicate) {
                return $this->errorResponse('This platform already exists for the target entity.', 422);
            }
        }

        $socialLink->update($request->validated());

        return $this->successResponse(
            $socialLink->fresh()->load('socialable'),
            'Social link updated.',
        );
    }

    public function destroy(SocialLink $socialLink): JsonResponse
    {
        $socialLink->delete();

        return $this->deletedResponse('Social link deleted.');
    }
}
