<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Portfolio\DuplicatePortfolioItemException;
use App\Exceptions\Portfolio\EstateAlreadyTakenException;
use App\Exceptions\Portfolio\EstateNotPublishedException;
use App\Exceptions\Portfolio\InvalidPortfolioStatusTransitionException;
use App\Exceptions\Portfolio\PortfolioDomainException;
use App\Exceptions\Portfolio\PortfolioItemNotFoundException;
use App\Http\Requests\StorePortfolioItemRequest;
use App\Http\Requests\UpdatePortfolioItemStatusRequest;
use App\Models\Estate;
use App\Models\PortfolioItem;
use App\Services\Portfolio\PortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Thin controller — delegates all portfolio rules to PortfolioService.
 */
class PortfolioController extends BaseApiController
{
    public function __construct(
        private readonly PortfolioService $portfolios,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $summary = $this->portfolios->getPortfolioSummary($request->user());

        return $this->successResponse($summary, 'Portfolio summary retrieved.');
    }

    public function store(StorePortfolioItemRequest $request): JsonResponse
    {
        $estate = Estate::query()->findOrFail($request->integer('estate_id'));

        try {
            $item = $this->portfolios->addEstateToPortfolio(
                $request->user(),
                $estate,
                $request->validated(),
            );
        } catch (PortfolioDomainException|InvalidArgumentException $e) {
            return $this->mapDomainException($e);
        }

        return $this->createdResponse($item, 'Estate added to portfolio.');
    }

    public function updateStatus(
        UpdatePortfolioItemStatusRequest $request,
        PortfolioItem $portfolioItem,
    ): JsonResponse {
        $this->authorize('update', $portfolioItem);

        try {
            $item = $this->portfolios->updatePortfolioItemStatus(
                $request->user(),
                $portfolioItem,
                $request->string('status')->toString(),
                $request->safe()->except(['status']),
            );
        } catch (PortfolioDomainException|InvalidArgumentException $e) {
            return $this->mapDomainException($e);
        }

        return $this->successResponse($item, 'Portfolio item updated.');
    }

    public function destroy(Request $request, PortfolioItem $portfolioItem): JsonResponse
    {
        $this->authorize('delete', $portfolioItem);

        $this->portfolios->removePortfolioItem($request->user(), $portfolioItem);

        return $this->deletedResponse('Portfolio item removed.');
    }

    public function destroyByEstate(Request $request, Estate $estate): JsonResponse
    {
        try {
            $this->portfolios->removeEstateFromPortfolio($request->user(), $estate);
        } catch (PortfolioItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }

        return $this->deletedResponse('Estate removed from portfolio.');
    }

    private function mapDomainException(PortfolioDomainException|InvalidArgumentException $e): JsonResponse
    {
        $status = match (true) {
            $e instanceof EstateNotPublishedException,
            $e instanceof DuplicatePortfolioItemException,
            $e instanceof EstateAlreadyTakenException,
            $e instanceof InvalidPortfolioStatusTransitionException,
            $e instanceof InvalidArgumentException => 422,
            default => 403,
        };

        return $this->errorResponse($e->getMessage(), $status);
    }
}
