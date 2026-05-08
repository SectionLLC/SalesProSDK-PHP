<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Configuration ─────────────────────────────────────────────────────────────

/**
 * Thrown when the SDK is misconfigured.
 */
class ConfigurationException extends SalesProException
{
    public function __construct(string $message)
    {
        parent::__construct($message, null, 500);
    }
}