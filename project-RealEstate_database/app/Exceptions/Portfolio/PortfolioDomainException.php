<?php

namespace App\Exceptions\Portfolio;

use RuntimeException;

/**
 * Base exception for portfolio domain rules (catch in API layer for HTTP mapping).
 */
abstract class PortfolioDomainException extends RuntimeException
{
    //
}
