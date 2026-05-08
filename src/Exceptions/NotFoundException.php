<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Not Found ─────────────────────────────────────────────────────────────────

/**
 * Thrown when a 404 Not Found response is received.
 */
class NotFoundException extends SalesProException
{
    public function __construct(string $message = 'Resource not found.')
    {
        parent::__construct($message, null, 404);
    }
}