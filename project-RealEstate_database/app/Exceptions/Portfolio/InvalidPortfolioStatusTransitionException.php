<?php

namespace App\Exceptions\Portfolio;

final class InvalidPortfolioStatusTransitionException extends PortfolioDomainException
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
    ) {
        parent::__construct("Invalid portfolio item status transition from [{$from}] to [{$to}].");
    }
}
