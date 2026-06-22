<?php

namespace App\Http\Controllers\Api\Investor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\MapsPortfolioDomainExceptions;
use App\Http\Requests\Portfolio\IndexMyPortfoliosRequest;
use App\Http\Requests\Portfolio\StorePortfolioRequest;
use App\Http\Resources\Portfolio\PortfolioResource;
use App\Models\Portfolio;
use App\Services\Portfolio\PortfolioService;
use Illuminate\Http\JsonResponse;

class MyPortfolioController extends BaseApiController
{
    use MapsPortfolioDomainExceptions;

    public function __construct(
        private readonly PortfolioService $portfolios,
    ) {}

    public function index(IndexMyPortfoliosRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Portfolio::class);

        $paginator = $this->portfolios->listPortfolios(
            $request->user(),
            $request->integer('per_page', 15),
        );

        return $this->successResponse(
            PortfolioResource::collection($paginator->items())->resolve(),
            'Portfolios retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function store(StorePortfolioRequest $request): JsonResponse
    {
        $this->authorize('create', Portfolio::class);

        $portfolio = $this->portfolios->createPortfolio(
            $request->user(),
            $request->validated(),
        );

        $portfolio->loadCount('items');

        return $this->createdResponse(
            (new PortfolioResource($portfolio))->resolve(),
            'Portfolio created.',
        );
    }
}
