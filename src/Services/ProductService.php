<?php
/**
 * ============================================================================
 * FILE: src/Services/ProductService.php
 * Product Management - 4 Endpoints
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Services;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Interfaces\ServiceInterface;
use SalesPro\SDK\Models\Product;
use SalesPro\SDK\Models\PaginationResult;
use SalesPro\SDK\Traits\RequestTrait;
use SalesPro\SDK\Traits\ResponseTrait;

class ProductService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /**
     * List products with filtering and pagination
     */
    public function list(array $filters = []): PaginationResult
    {
        $endpoint = '/connector/api/list-products';
        $response = $this->get($endpoint, $filters);
        
        return PaginationResult::fromResponse($response);
    }
    
    /**
     * Get single product by ID
     */
    public function find(int $productId): Product
    {
        $endpoint = "/connector/api/get-product/{$productId}";
        $response = $this->get($endpoint);
        
        return Product::fromArray($response['data'] ?? []);
    }
    
    /**
     * List product variations
     */
    public function variations(array $filters = []): array
    {
        $endpoint = '/connector/api/variations';
        return $this->get($endpoint, $filters);
    }
    
    /**
     * List selling price groups
     */
    public function sellingPriceGroups(array $filters = []): array
    {
        $endpoint = '/connector/api/selling-price-groups';
        return $this->get($endpoint, $filters);
    }
}