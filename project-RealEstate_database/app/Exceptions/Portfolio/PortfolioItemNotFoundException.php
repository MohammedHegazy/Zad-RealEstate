<?php

namespace App\Exceptions\Portfolio;

final class PortfolioItemNotFoundException extends PortfolioDomainException
{
    public function __construct(
        public readonly int $userId,
        public readonly int $estateId,
    ) {
        parent::__construct(
            "Portfolio item for estate {$estateId} was not found for user {$userId}."
        );
    }
}
