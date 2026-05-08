<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;

// ── Base ──────────────────────────────────────────────────────────────────────

/**
 * Base exception for all SalesPro SDK errors.
 */
class SalesProException extends \RuntimeException
{
    /** @var array<string, mixed>|null Raw API error payload */
    protected ?array $apiError;

    public function __construct(
        string $message = '',
        ?array $apiError = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->apiError = $apiError;
        parent::__construct($message, $code, $previous);
    }

    /** Returns the raw API error payload, if any. */
    public function getApiError(): ?array
    {
        return $this->apiError;
    }
}