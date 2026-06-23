<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\Portfolio\DuplicatePortfolioItemException;
use App\Exceptions\Portfolio\EstateAlreadyTakenException;
use App\Exceptions\Portfolio\EstateNotPublishedException;
use App\Exceptions\Portfolio\InvalidPortfolioStatusTransitionException;
use App\Exceptions\Portfolio\PortfolioDomainException;
use App\Exceptions\Portfolio\UnauthorizedPortfolioAccessException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

trait MapsPortfolioDomainExceptions
{
    protected function portfolioDomainErrorResponse(
        PortfolioDomainException|InvalidArgumentException $e,
    ): JsonResponse {
        $status = match (true) {
            $e instanceof EstateNotPublishedException,
            $e instanceof DuplicatePortfolioItemException,
            $e instanceof EstateAlreadyTakenException,
            $e instanceof InvalidPortfolioStatusTransitionException,
            $e instanceof InvalidArgumentException => 422,
            $e instanceof UnauthorizedPortfolioAccessException => 403,
            default => 400,
        };

        return $this->errorResponse($e->getMessage(), $status);
    }
}
