<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Table ─────────────────────────────────────────────────────────────────────

/**
 * TableService — Restaurant / service table management.
 *
 * Docs: connector/api/table
 */
class TableService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/table', [], true);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/table/{$id}", [], true);
    }
}