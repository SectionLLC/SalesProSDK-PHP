<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Http;
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