<?php

namespace App\Exceptions\Portfolio;

final class EstateNotPublishedException extends PortfolioDomainException
{
    public function __construct(
        public readonly int $estateId,
        public readonly string $status,
    ) {
        parent::__construct(
            "Estate {$estateId} is not published for portfolio tracking (status: {$status})."
        );
    }
}
