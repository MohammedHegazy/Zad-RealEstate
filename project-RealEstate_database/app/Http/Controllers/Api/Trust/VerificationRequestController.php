<?php

namespace App\Http\Controllers\Api\Trust;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Trust\StoreVerificationRequestRequest;
use App\Http\Resources\Trust\VerificationRequestResource;
use App\Services\Trust\VerificationRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationRequestController extends BaseApiController
{
    public function __construct(
        private readonly VerificationRequestService $verifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->verifications->listForUser(
            $request->user(),
            $request->integer('per_page', 15),
        );

        return $this->successResponse(
            VerificationRequestResource::collection($paginator->items())->resolve(),
            'Verification requests retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function store(StoreVerificationRequestRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\VerificationRequest::class);

        $verification = $this->verifications->submit(
            $request->user(),
            $request->validated(),
            $request->file('document'),
        );

        return $this->createdResponse(
            (new VerificationRequestResource($verification))->resolve(),
            'Verification request submitted.',
        );
    }
}
