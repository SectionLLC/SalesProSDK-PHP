<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Http;

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