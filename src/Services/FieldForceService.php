<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Field Force ───────────────────────────────────────────────────────────────

/**
 * FieldForceService — Field sales visit tracking.
 *
 * Docs: connector/api/field-force
 */
class FieldForceService extends AbstractService
{
    public function listVisits(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/field-force', $this->compact($params));
    }

    /**
     * @param array{
     *   contact_id: int,
     *   visit_date: string,
     *   note?: string,
     *   latitude?: string,
     *   longitude?: string
     * } $data
     */
    public function createVisit(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/field-force/create', $data);
    }

    /**
     * @param array{status: string, note?: string} $data
     */
    public function updateVisitStatus(int $id, array $data): ActionResponse
    {
        return $this->postAction("connector/api/field-force/update-visit-status/{$id}", $data);
    }
}