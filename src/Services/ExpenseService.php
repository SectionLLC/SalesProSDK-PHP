<?php
/**
 * ============================================================================
 * FILE: src/Services/ExpenseService.php
 * Expense Management - 6 Endpoints
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Services;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Interfaces\ServiceInterface;
use SalesPro\SDK\Models\Expense;
use SalesPro\SDK\Models\PaginationResult;
use SalesPro\SDK\Traits\RequestTrait;
use SalesPro\SDK\Traits\ResponseTrait;

class ExpenseService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List expenses */
    public function list(array $filters = []): PaginationResult { return PaginationResult::fromResponse($this->get('/connector/api/expenses', $filters)); }
    
    /** Create expense */
    public function create(array $data): Expense { $this->validate($data); return Expense::fromArray($this->post('/connector/api/expenses', $data)['data'] ?? []); }
    
    /** Get expense by ID */
    public function find(int $id): Expense { return Expense::fromArray($this->get("/connector/api/expenses/{$id}")['data'] ?? []); }
    
    /** Update expense */
    public function update(int $id, array $data): Expense { $this->put("/connector/api/expenses/{$id}", $id, $data); return Expense::fromArray($data); }
    
    /** List refunds */
    public function listRefunds(array $filters = []): array { return $this->get('/connector/api/expense-refunds', $filters); }
    
    /** List categories */
    public function categories(): array { return $this->get('/connector/api/expense-categories'); }
    
    private function validate(array $data): void {
        if (!isset($data['final_total'])) throw new \InvalidArgumentException('final_total is required');
        if (!isset($data['expense_category_id'])) throw new \InvalidArgumentException('expense_category_id is required');
    }
}