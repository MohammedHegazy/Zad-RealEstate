<?php

namespace App\Http\Controllers\Api\Investor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Concerns\MapsPortfolioDomainExceptions;
use App\Http\Requests\Portfolio\IndexMyPortfoliosRequest;
use App\Http\Requests\Portfolio\StorePortfolioRequest;
use App\Http\Resources\Portfolio\PortfolioItemResource;
use App\Http\Resources\Portfolio\PortfolioResource;
use App\Models\InvestmentPortfolio;
use App\Models\Portfolio;
use App\Services\Portfolio\PortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestmentPortfolioController extends BaseApiController
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
            'Investment portfolios retrieved.',
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

        $portfolio->loadCount('properties');

        return $this->createdResponse(
            (new PortfolioResource($portfolio))->resolve(),
            'Investment portfolio created.',
        );
    }

    public function show(Request $request, InvestmentPortfolio $investmentPortfolio): JsonResponse
    {
        $this->authorize('view', $investmentPortfolio);

        $portfolio = $this->portfolios->showPortfolio($request->user(), $investmentPortfolio);
        $portfolio->total_invested = $this->portfolios->calculateTotalInvestedAmount(
            $request->user(),
            $portfolio,
        );

        return $this->successResponse(
            (new PortfolioResource($portfolio))->resolve(),
            'Investment portfolio retrieved.',
        );
    }

    public function properties(Request $request, InvestmentPortfolio $investmentPortfolio): JsonResponse
    {
        $this->authorize('view', $investmentPortfolio);

        try {
            $properties = $this->portfolios->listPortfolioProperties(
                $request->user(),
                $investmentPortfolio,
            );
        } catch (\App\Exceptions\Portfolio\UnauthorizedPortfolioAccessException $e) {
            return $this->portfolioDomainErrorResponse($e);
        }

        return $this->successResponse(
            [
                'properties' => PortfolioItemResource::collection($properties)->resolve(),
                'total_invested' => $this->portfolios->calculateTotalInvestedAmount(
                    $request->user(),
                    $investmentPortfolio,
                ),
            ],
            'Portfolio properties retrieved.',
        );
    }
}
