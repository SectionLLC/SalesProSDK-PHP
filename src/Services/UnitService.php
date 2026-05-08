<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Unit ──────────────────────────────────────────────────────────────────────

/**
 * UnitService — Units of measurement (kg, litre, pcs, etc.).
 *
 * Docs: connector/api/unit
 */
class UnitService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/unit', [], true);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/unit/{$id}", [], true);
    }
}