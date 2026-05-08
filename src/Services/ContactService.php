<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Contact ───────────────────────────────────────────────────────────────────

/**
 * ContactService — Customer / supplier CRUD and payment recording.
 *
 * Docs: connector/api/contactapi
 */
class ContactService extends AbstractService
{
    /**
     * @param array{page?: int, per_page?: int, type?: string} $params
     */
    public function list(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/contactapi', $this->compact($params));
    }

    /**
     * Create a new contact (customer or supplier).
     *
     * @param array{
     *   type: string,
     *   first_name: string,
     *   last_name?: string,
     *   supplier_business_name?: string,
     *   email?: string,
     *   mobile?: string,
     *   address_line_1?: string,
     *   address_line_2?: string,
     *   city?: string,
     *   state?: string,
     *   country?: string,
     *   zip_code?: string,
     *   tax_number?: string,
     *   credit_limit?: float,
     *   opening_balance?: float,
     *   pay_term_number?: int,
     *   pay_term_type?: string,
     *   customer_group_id?: int,
     *   custom_field1?: string,
     *   custom_field2?: string,
     *   custom_field3?: string,
     *   custom_field4?: string
     * } $data
     */
    public function create(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/contactapi', $data);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/contactapi/{$id}");
    }

    /**
     * Update an existing contact.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): ApiResponse
    {
        return $this->putSingle("connector/api/contactapi/{$id}", $data);
    }

    /**
     * Record a payment against a contact's balance.
     *
     * @param array{
     *   contact_id: int,
     *   amount: float,
     *   method: string,
     *   paid_on?: string,
     *   note?: string,
     *   account_id?: int
     * } $data
     */
    public function addPayment(array $data): ActionResponse
    {
        return $this->postAction('connector/api/contactapi/payment', $data);
    }
}