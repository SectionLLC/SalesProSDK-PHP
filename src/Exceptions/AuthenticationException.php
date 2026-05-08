<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;

// ── Authentication ────────────────────────────────────────────────────────────

/**
 * Thrown when a 401 Unauthorized response is received from the API.
 */
class AuthenticationException extends SalesProException
{
    public function __construct(string $message = 'Authentication failed.', ?array $apiError = null)
    {
        parent::__construct($message, $apiError, 401);
    }
}