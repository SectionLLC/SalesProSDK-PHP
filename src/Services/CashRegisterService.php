<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Cash Register ─────────────────────────────────────────────────────────────

/**
 * CashRegisterService — POS till management.
 *
 * Docs: connector/api/cash-register
 */
class CashRegisterService extends AbstractService
{
    public function list(): ApiListResponse
    {
        return $this->getList('connector/api/cash-register');
    }

    /**
     * @param array{location_id: int, closing_amount: float, closing_note?: string} $data
     */
    public function create(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/cash-register', $data);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/cash-register/{$id}");
    }
}