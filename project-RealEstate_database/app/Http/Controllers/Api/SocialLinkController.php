<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SyncSocialLinksRequest;
use App\Models\Estate;
use App\Models\SocialLink;
use App\Services\SocialLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialLinkController extends BaseApiController
{
    public function __construct(
        private readonly SocialLinkService $socialLinks,
    ) {}

    public function myLinks(Request $request): JsonResponse
    {
        $user = $request->user()->load('socialLinks');

        return $this->successResponse(
            $this->socialLinks->formatCollection($user),
            'Your social links retrieved.',
        );
    }

    public function updateMyLinks(SyncSocialLinksRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->socialLinks->syncFromRequest($user, $request->validated(), replace: true);

        return $this->successResponse(
            $this->socialLinks->formatCollection($user->fresh()->load('socialLinks')),
            'Social links updated.',
        );
    }

    public function show(SocialLink $socialLink): JsonResponse
    {
        return $this->successResponse(
            $socialLink->only(['id', 'platform', 'url', 'socialable_type', 'socialable_id']),
            'Social link retrieved.',
        );
    }

    public function updateEstateLinks(SyncSocialLinksRequest $request, Estate $estate): JsonResponse
    {
        if ($estate->user_id !== $request->user()->id) {
            return $this->errorResponse('You can only update social links for your own estates.', 403);
        }

        $this->socialLinks->syncFromRequest($estate, $request->validated(), replace: true);

        return $this->successResponse(
            $this->socialLinks->formatCollection($estate->fresh()->load('socialLinks')),
            'Estate social links updated.',
        );
    }
}
