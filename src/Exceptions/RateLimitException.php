<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Rate Limit ────────────────────────────────────────────────────────────────

/**
 * Thrown when the API returns a 429 Too Many Requests response.
 */
class RateLimitException extends SalesProException
{
    private ?int $retryAfter;

    public function __construct(?int $retryAfterSeconds = null)
    {
        $this->retryAfter = $retryAfterSeconds;
        $msg = 'Rate limit exceeded.';
        if ($retryAfterSeconds !== null) {
            $msg .= " Retry after {$retryAfterSeconds} seconds.";
        }
        parent::__construct($msg, null, 429);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}