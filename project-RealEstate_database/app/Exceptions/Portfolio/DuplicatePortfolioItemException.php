<?php

namespace App\Exceptions\Portfolio;

final class DuplicatePortfolioItemException extends PortfolioDomainException
{
    public function __construct(
        public readonly int $portfolioId,
        public readonly int $estateId,
    ) {
        parent::__construct(
            "Estate {$estateId} is already in portfolio {$portfolioId}."
        );
    }
}
