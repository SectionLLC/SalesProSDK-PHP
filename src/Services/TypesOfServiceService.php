<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Types of Service ──────────────────────────────────────────────────────────

/**
 * TypesOfServiceService — Dine-in, takeaway, delivery etc.
 *
 * Docs: connector/api/types-of-service
 */
class TypesOfServiceService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/types-of-service', [], true);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/types-of-service/{$id}", [], true);
    }
}