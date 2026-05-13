<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Http;

/**
 * ApiResponse — Generic wrapper for single-item API responses.
 *
 * @template T
 */
class ApiResponse
{
    /**
     * @param mixed         $data    The deserialized response data
     * @param bool          $success Whether the API indicated success
     * @param string|null   $message Optional message from the API
     * @param array|null    $error   Raw error payload if the request failed
     */
    public function __construct(
        public readonly mixed  $data,
        public readonly bool   $success = true,
        public readonly ?string $message = null,
        public readonly ?array  $error   = null
    ) {}

    /**
     * Creates an ApiResponse from a raw decoded JSON array.
     *
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            data:    $raw['data'] ?? $raw,
            success: (bool) ($raw['success'] ?? true),
            message: $raw['message'] ?? $raw['msg'] ?? null,
            error:   $raw['error'] ?? null,
        );
    }
}