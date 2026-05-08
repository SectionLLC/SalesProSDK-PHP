<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Server Error ──────────────────────────────────────────────────────────────

/**
 * Thrown when the API returns a 5xx Server Error response.
 */
class ServerException extends SalesProException
{
    public function __construct(int $statusCode, string $body = '')
    {
        parent::__construct("Server error ({$statusCode}): {$body}", null, $statusCode);
    }
}