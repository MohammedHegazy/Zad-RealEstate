<?php

namespace App\Http\Controllers\Api\Trust;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Trust\TrustScoreResource;
use App\Models\Agent;
use App\Models\Companies;
use App\Services\Trust\TrustScoreService;
use Illuminate\Http\JsonResponse;

class TrustScoreController extends BaseApiController
{
    public function __construct(
        private readonly TrustScoreService $trustScore,
    ) {}

    public function forAgent(Agent $agent): JsonResponse
    {
        $score = $this->trustScore->forAgent($agent);

        return $this->successResponse(
            (new TrustScoreResource($score))->resolve(),
            'Agent trust score retrieved.',
        );
    }

    public function forCompany(Companies $company): JsonResponse
    {
        $score = $this->trustScore->forCompany($company);

        return $this->successResponse(
            (new TrustScoreResource($score))->resolve(),
            'Company trust score retrieved.',
        );
    }
}
