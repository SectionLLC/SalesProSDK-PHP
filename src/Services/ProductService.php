<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Product ───────────────────────────────────────────────────────────────────

/**
 * ProductService — Product catalog, variations, stock reports, and price groups.
 *
 * Docs: connector/api/product
 *       connector/api/variation/{id}
 *       connector/api/selling-price-group
 *       connector/api/product-stock-report
 */
class ProductService extends AbstractService
{
    public function list(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/product', $this->compact($params), true);
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/product/{$id}", [], true);
    }

    public function listVariations(int $productId): ApiListResponse
    {
        return $this->getList("connector/api/variation/{$productId}", [], true);
    }

    public function listSellingPriceGroups(): ApiListResponse
    {
        return $this->getList('connector/api/selling-price-group', [], true);
    }

    public function getStockReport(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/product-stock-report', $this->compact($params));
    }
}