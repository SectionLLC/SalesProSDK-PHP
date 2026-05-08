<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Authorisation ─────────────────────────────────────────────────────────────

/**
 * Thrown when a 403 Forbidden response is received.
 */
class AuthorizationException extends SalesProException
{
    public function __construct(string $message = 'Access denied.')
    {
        parent::__construct($message, null, 403);
    }
}