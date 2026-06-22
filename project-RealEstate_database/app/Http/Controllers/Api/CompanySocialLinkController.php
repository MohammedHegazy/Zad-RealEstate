<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesOwnedCompany;
use App\Http\Requests\StoreSocialLinkRequest;
use App\Http\Requests\UpdateSocialLinkRequest;
use App\Models\Companies;
use App\Models\SocialLink;
use App\Services\SocialLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanySocialLinkController extends BaseApiController
{
    use ResolvesOwnedCompany;

    public function __construct(
        private readonly SocialLinkService $socialLinks,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        return $this->successResponse(
            $this->socialLinks->formatCollection($company->load('socialLinks')),
            'Company social links retrieved.',
        );
    }

    public function store(StoreSocialLinkRequest $request): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company) {
            return $this->notFoundResponse('Company not found.');
        }

        $this->socialLinks->assertPlatformAvailable($company, $request->platform);

        $link = $company->socialLinks()->create($request->validated());

        return $this->createdResponse($link, 'Social link added.');
    }

    public function update(Request $request, SocialLink $socialLink): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company || ! $this->linkBelongsTo($socialLink, $company)) {
            return $this->notFoundResponse('Social link not found.');
        }

        $data = $request->validate([
            'platform' => ['sometimes', 'string', Rule::in(config('realestate.social_platforms'))],
            'url' => ['sometimes', 'url', 'max:500'],
        ]);

        if (isset($data['platform']) && $data['platform'] !== $socialLink->platform) {
            $this->socialLinks->assertPlatformAvailable($company, $data['platform'], $socialLink->id);
        }

        $socialLink->update($data);

        return $this->successResponse($socialLink->fresh(), 'Social link updated.');
    }

    public function destroy(Request $request, SocialLink $socialLink): JsonResponse
    {
        $company = $this->ownedCompany($request);

        if (! $company || ! $this->linkBelongsTo($socialLink, $company)) {
            return $this->notFoundResponse('Social link not found.');
        }

        $socialLink->delete();

        return $this->deletedResponse('Social link deleted.');
    }

    private function linkBelongsTo(SocialLink $socialLink, Companies $company): bool
    {
        return $socialLink->socialable_type === Companies::class
            && (int) $socialLink->socialable_id === (int) $company->id;
    }
}
