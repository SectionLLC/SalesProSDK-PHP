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

/**
 * ApiListResponse — Wrapper for paginated list responses.
 *
 * @template T
 */
class ApiListResponse
{
    /**
     * @param array<int, mixed>  $data       The list of items
     * @param PaginationMeta|null $meta       Pagination metadata
     * @param bool               $success    Whether the request succeeded
     * @param string|null        $message    Optional API message
     */
    public function __construct(
        public readonly array          $data,
        public readonly ?PaginationMeta $meta    = null,
        public readonly bool            $success = true,
        public readonly ?string         $message = null,
    ) {}

    /**
     * Creates an ApiListResponse from a raw decoded JSON array.
     *
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        $data = $raw['data'] ?? (isset($raw[0]) ? $raw : []);
        $meta = null;

        if (isset($raw['meta'])) {
            $meta = PaginationMeta::fromArray($raw['meta']);
        } elseif (isset($raw['current_page'])) {
            $meta = PaginationMeta::fromArray($raw);
        }

        return new self(
            data:    is_array($data) ? array_values($data) : [],
            meta:    $meta,
            success: (bool) ($raw['success'] ?? true),
            message: $raw['message'] ?? null,
        );
    }

    /** Whether there are more pages to fetch. */
    public function hasMorePages(): bool
    {
        return $this->meta !== null && $this->meta->currentPage < $this->meta->lastPage;
    }

    /** Total number of items across all pages. */
    public function total(): int
    {
        return $this->meta?->total ?? count($this->data);
    }
}

/**
 * PaginationMeta — Metadata returned alongside paginated lists.
 */
class PaginationMeta
{
    public function __construct(
        public readonly int     $currentPage,
        public readonly int     $total,
        public readonly int     $perPage,
        public readonly int     $lastPage,
        public readonly ?string $nextPageUrl = null,
        public readonly ?string $prevPageUrl = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPage: (int) ($data['current_page'] ?? 1),
            total:       (int) ($data['total'] ?? 0),
            perPage:     (int) ($data['per_page'] ?? 15),
            lastPage:    (int) ($data['last_page'] ?? 1),
            nextPageUrl: $data['next_page_url'] ?? null,
            prevPageUrl: $data['prev_page_url'] ?? null,
        );
    }
}

/**
 * ActionResponse — Returned by non-CRUD action endpoints.
 */
class ActionResponse
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $msg  = null,
        public readonly ?string $type = null,
        public readonly mixed   $data = null,
    ) {}

    /** @param array<string, mixed> $raw */
    public static function fromArray(array $raw): self
    {
        return new self(
            success: (bool) ($raw['success'] ?? isset($raw['msg'])),
            msg:     $raw['msg'] ?? $raw['message'] ?? null,
            type:    $raw['type'] ?? null,
            data:    $raw['data'] ?? null,
        );
    }
}