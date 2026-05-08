<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Tax ───────────────────────────────────────────────────────────────────────

/**
 * TaxService — Tax rate management.
 *
 * Docs: connector/api/tax
 */
class TaxService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/tax', [], true);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/tax/{$id}", [], true);
    }
}