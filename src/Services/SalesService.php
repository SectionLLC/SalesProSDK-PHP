<?php
/**
 * ============================================================================
 * FILE: src/Services/SalesService.php
 * Sales Management - 8 Endpoints - Complete CRUD with C#-Style Models
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Services;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Interfaces\ServiceInterface;
use SalesPro\SDK\Models\Sell;
use SalesPro\SDK\Models\PaginationResult;
use SalesPro\SDK\Exceptions\ValidationException;
use SalesPro\SDK\Traits\RequestTrait;
use SalesPro\SDK\Traits\ResponseTrait;

class SalesService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /**
     * List sells with filtering
     */
    public function list(array $filters = []): PaginationResult
    {
        $endpoint = '/connector/api/list-sells';
        $response = $this->get($endpoint, $filters);
        
        return PaginationResult::fromResponse($response);
    }
    
    /**
     * Create new sell transaction
     */
    public function create(array $data): Sell
    {
        $this->validateCreateData($data);
        
        $endpoint = '/connector/api/create-sell';
        $response = $this->post($endpoint, $data);
        
        return Sell::fromArray($response['data'] ?? []);
    }
    
    /**
     * Get single sell by ID
     */
    public function find(int $sellId): Sell
    {
        $endpoint = "/connector/api/get-sell/{$sellId}";
        $response = $this->get($endpoint);
        
        return Sell::fromArray($response['data'] ?? []);
    }
    
    /**
     * Update existing sell
     */
    public function update(int $sellId, array $data): Sell
    {
        $endpoint = "/connector/api/update-sell/{$sellId}";
        $response = $this->put($endpoint, $sellId, $data);
        
        return Sell::fromArray($data['data'] ?? []);
    }
    
    /**
     * Delete a sell
     */
    public function delete(int $sellId): bool
    {
        try {
            $this->delete("/connector/api/delete-sell/{$sellId}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Add sell return
     */
    public function addReturn(int $sellId, array $returnData): array
    {
        $endpoint = '/connector_api/sell-return';
        $data = array_merge(['transaction_id' => $sellId], $returnData);
        
        return $this->post($endpoint, $data);
    }
    
    /**
     * List sell returns
     */
    public function listReturns(array $filters = []): array
    {
        return $this->get('/connector_api/sell-return', $filters);
    }
    
    /**
     * Update shipping status
     */
    public function updateShippingStatus(int $sellId, string $status, ?string $trackingNumber = null): array
    {
        $endpoint = '/connector_api/update-shipping-status';
        $data = [
            'transaction_id' => $sellId,
            'shipping_status' => $status,
            'tracking_number' => $trackingNumber
        ];
        
        return $this->post($endpoint, $data);
    }
    
    /**
     * Validate create sell data
     */
    private function validateCreate(array $data): void
    {
        if (!isset($data['products']) || empty($data['products'])) {
            throw new ValidationException('Products array is required', 422, ['products' => ['Products array is required']]);
        }
        
        if (!isset($data['payment']) || empty($data['payment'])) {
            throw new ValidationException('Payment array is required', 422, ['payment' => ['Payment array is required']]);
        }
        
        // Validate location_id
        if (!isset($data['location_id']) || empty($data['location_id'])) {
            throw new ValidationException('Location ID is required', 422, ['location_id' => ['Location ID is required']]);
        }
        
        foreach ($data['products'] as $i => $product) {
            if (!isset($product['product_id'])) {
                throw new ValidationException("Product #{$i}: product_id is required", 422, ["products.{$i}" => ["product_id is required"]]);
            }
            
            if (!isset($product['quantity'])) {
                throw new ValidationException("Product #{$i}: quantity is required", 422, ["products.{$i}" => ["quantity is required"]]);
            }
        }
    }
}