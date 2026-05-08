<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Sales ─────────────────────────────────────────────────────────────────────

/**
 * SalesService — Full sell lifecycle: create, read, update, delete, returns, shipping.
 *
 * Docs: connector/api/sell
 *       connector/api/sell-return
 *       connector/api/list-sell-return
 *       connector/api/update-shipping-status
 */
class SalesService extends AbstractService
{
    public function list(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/sell', $this->compact($params));
    }

    /**
     * Create a new sale (sell transaction).
     *
     * @param array{
     *   location_id: int,
     *   contact_id: int,
     *   transaction_date: string,
     *   status?: string,
     *   payment_status?: string,
     *   discount_type?: string,
     *   discount_amount?: float,
     *   tax_rate_id?: int,
     *   shipping_charges?: float,
     *   shipping_status?: string,
     *   shipping_details?: string,
     *   delivered_to?: string,
     *   sell_lines: array<int, array{
     *     product_id: int,
     *     variation_id?: int,
     *     quantity: float,
     *     unit_price?: float,
     *     line_discount_type?: string,
     *     line_discount_amount?: float,
     *     tax_id?: int,
     *     sell_line_note?: string
     *   }>,
     *   payments?: array<int, array{
     *     method: string,
     *     amount: float,
     *     card_transaction_number?: string,
     *     note?: string,
     *     account_id?: int
     *   }>
     * } $data
     */
    public function create(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/sell', $data);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/sell/{$id}");
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ApiResponse
    {
        return $this->putSingle("connector/api/sell/{$id}", $data);
    }

    public function delete(int $id): ActionResponse
    {
        return $this->deleteSingle("connector/api/sell/{$id}");
    }

    /**
     * Record a sell return / credit note.
     *
     * @param array{
     *   transaction_id: int,
     *   transaction_date?: string,
     *   return_lines: array<int, array{sell_line_id: int, quantity: float}>,
     *   payments?: array<int, array{method: string, amount: float}>
     * } $data
     */
    public function addReturn(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/sell-return', $data);
    }

    public function listReturns(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/list-sell-return', $this->compact($params));
    }

    /**
     * Update the shipping status of a transaction.
     *
     * @param array{
     *   transaction_id: int,
     *   shipping_status: string,
     *   shipping_details?: string,
     *   delivered_to?: string
     * } $data
     */
    public function updateShippingStatus(array $data): ActionResponse
    {
        return $this->postAction('connector/api/update-shipping-status', $data);
    }
}