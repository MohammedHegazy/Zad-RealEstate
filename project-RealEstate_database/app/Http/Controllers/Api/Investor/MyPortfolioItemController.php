<?php

namespace App\Http\Controllers\Api\Investor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\MapsPortfolioDomainExceptions;
use App\Http\Requests\Portfolio\IndexMyPortfolioItemsRequest;
use App\Http\Requests\Portfolio\StorePortfolioItemRequest;
use App\Http\Requests\Portfolio\UpdatePortfolioItemRequest;
use App\Http\Resources\Portfolio\PortfolioItemResource;
use App\Models\Estate;
use App\Models\PortfolioItem;
use App\Services\Portfolio\PortfolioService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class MyPortfolioItemController extends BaseApiController
{
    use MapsPortfolioDomainExceptions;

    public function __construct(
        private readonly PortfolioService $portfolios,
    ) {}

    public function index(IndexMyPortfolioItemsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', PortfolioItem::class);

        try {
            $paginator = $this->portfolios->listPortfolioItems(
                $request->user(),
                $request->safe()->only(['portfolio_id', 'status']),
                $request->integer('per_page', 15),
            );
        } catch (\App\Exceptions\Portfolio\UnauthorizedPortfolioAccessException $e) {
            return $this->portfolioDomainErrorResponse($e);
        }

        return $this->successResponse(
            PortfolioItemResource::collection($paginator->items())->resolve(),
            'Portfolio items retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function store(StorePortfolioItemRequest $request): JsonResponse
    {
        $this->authorize('create', PortfolioItem::class);

        $estate = Estate::query()->findOrFail($request->integer('estate_id'));

        try {
            $item = $this->portfolios->addEstateToPortfolio(
                $request->user(),
                $estate,
                $request->validated(),
            );
        } catch (\App\Exceptions\Portfolio\PortfolioDomainException|InvalidArgumentException $e) {
            return $this->portfolioDomainErrorResponse($e);
        }

        $item->load(['portfolio', 'estate.place']);

        return $this->createdResponse(
            (new PortfolioItemResource($item))->resolve(),
            'Estate added to portfolio.',
        );
    }

    public function update(UpdatePortfolioItemRequest $request, int $id): JsonResponse
    {
        $item = PortfolioItem::query()->findOrFail($id);

        $this->authorize('update', $item);

        try {
            $updated = $this->portfolios->updatePortfolioItem(
                $request->user(),
                $item,
                $request->validated(),
            );
        } catch (\App\Exceptions\Portfolio\PortfolioDomainException|InvalidArgumentException $e) {
            return $this->portfolioDomainErrorResponse($e);
        }

        $updated->load(['portfolio', 'estate.place']);

        return $this->successResponse(
            (new PortfolioItemResource($updated))->resolve(),
            'Portfolio item updated.',
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $item = PortfolioItem::query()->findOrFail($id);

        $this->authorize('delete', $item);

        $this->portfolios->removePortfolioItem(request()->user(), $item);

        return $this->deletedResponse('Portfolio item removed.');
    }

    public function storeForPortfolio(StorePortfolioItemRequest $request, int $investment_portfolio): JsonResponse
    {
        $this->authorize('create', PortfolioItem::class);

        $estate = Estate::query()->findOrFail($request->integer('estate_id'));

        $payload = array_merge($request->validated(), [
            'portfolio_id' => $investment_portfolio,
        ]);

        try {
            $item = $this->portfolios->addEstateToPortfolio(
                $request->user(),
                $estate,
                $payload,
            );
        } catch (\App\Exceptions\Portfolio\PortfolioDomainException|InvalidArgumentException $e) {
            return $this->portfolioDomainErrorResponse($e);
        }

        $item->load(['portfolio', 'estate.place']);

        return $this->createdResponse(
            (new PortfolioItemResource($item))->resolve(),
            'Estate added to portfolio.',
        );
    }

    public function destroyForPortfolio(int $investment_portfolio, int $property): JsonResponse
    {
        $item = PortfolioItem::query()
            ->where('portfolio_id', $investment_portfolio)
            ->findOrFail($property);

        $this->authorize('delete', $item);

        $this->portfolios->removePortfolioItem(request()->user(), $item);

        return $this->deletedResponse('Property removed from portfolio.');
    }
}
