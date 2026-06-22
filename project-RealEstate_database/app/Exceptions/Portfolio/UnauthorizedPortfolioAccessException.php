<?php

namespace App\Exceptions\Portfolio;

final class UnauthorizedPortfolioAccessException extends PortfolioDomainException
{
    public function __construct(string $message = 'You do not have access to this portfolio item.')
    {
        parent::__construct($message);
    }
}
