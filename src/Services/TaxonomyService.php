<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Taxonomy ──────────────────────────────────────────────────────────────────

/**
 * TaxonomyService — Product categories and sub-categories.
 *
 * Docs: connector/api/taxonomy
 */
class TaxonomyService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/taxonomy', [], true);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/taxonomy/{$id}", [], true);
    }
}