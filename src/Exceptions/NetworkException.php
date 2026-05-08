<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Network ───────────────────────────────────────────────────────────────────

/**
 * Thrown when a network-level failure occurs (timeout, DNS failure, etc.).
 */
class NetworkException extends SalesProException
{
    public function __construct(string $message, \Throwable $previous)
    {
        parent::__construct($message, null, 0, $previous);
    }
}