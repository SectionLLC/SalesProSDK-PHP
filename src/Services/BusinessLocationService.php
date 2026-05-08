<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Business Location ─────────────────────────────────────────────────────────

/**
 * BusinessLocationService — Branch / outlet management.
 *
 * Docs: connector/api/business-location
 */
class BusinessLocationService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/business-location', [], true);
    }

    public function get(int $id): ApiListResponse
    {
        return $this->getList("connector/api/business-location/{$id}", [], true);
    }
}