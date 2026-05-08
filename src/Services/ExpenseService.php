<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Expense ───────────────────────────────────────────────────────────────────

/**
 * ExpenseService — Expense CRUD, refunds, and categories.
 *
 * Docs: connector/api/expense
 *       connector/api/expense-refund
 *       connector/api/expense-categories
 */
class ExpenseService extends AbstractService
{
    public function list(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/expense', $this->compact($params));
    }

    /**
     * @param array{
     *   location_id: int,
     *   expense_category_id: int,
     *   final_total: float,
     *   transaction_date?: string,
     *   note?: string,
     *   ref_no?: string,
     *   tax_id?: int,
     *   payment_method?: string,
     *   account_id?: int
     * } $data
     */
    public function create(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/expense', $data);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/expense/{$id}");
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ApiResponse
    {
        return $this->putSingle("connector/api/expense/{$id}", $data);
    }

    public function listRefunds(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/expense-refund', $this->compact($params));
    }

    public function listCategories(): ApiListResponse
    {
        return $this->getList('connector/api/expense-categories', [], true);
    }
}