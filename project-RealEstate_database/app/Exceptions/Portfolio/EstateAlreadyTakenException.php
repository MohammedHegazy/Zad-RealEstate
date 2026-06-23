<?php

namespace App\Exceptions\Portfolio;

class EstateAlreadyTakenException extends PortfolioDomainException
{
    public function __construct(int $estateId)
    {
        parent::__construct("Estate [{$estateId}] is already invested or sold by another user.");
    }
}
