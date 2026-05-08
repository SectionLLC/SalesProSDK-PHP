<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;

// ── Brand ─────────────────────────────────────────────────────────────────────

/**
 * BrandService — Product brand catalog.
 *
 * Docs: connector/api/brand
 */
class BrandService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/brand', [], true);
    }

    public function get(string $id): ApiListResponse
    {
        return $this->getList("connector/api/brand/{$id}", [], true);
    }
}