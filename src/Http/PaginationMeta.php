<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Http;

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