<?php
/**
 * ============================================================================
 * FILE: src/Traits/ResponseTrait.php
 * Response Parsing & Formatting Helpers Trait
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Traits;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Models\PaginationResult;

trait ResponseTrait
{
    /**
     * Parse API response into standardized format
     *
     * @param string $response Raw JSON string
     * @return array Parsed response array
     */
    protected function parseResponse(string $response): array
    {
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON: " . json_last_error_msg());
        }
        
        // Ensure we have data wrapper
        if (array_key_exists('data', $data) && is_array($data['data'])) {
            $data = ['data' => $data];
        }
        
        return $data;
    }
    
    /**
     * Wrap successful response in standard format
     *
     * @param mixed $data Response data
     * @return ApiResponse object
     */
    protected function successResponse(mixed $data = null): array
    {
        return [
            'success' => true,
            'status' => 200,
            'message' => 'Success',
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Create error response
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array $additional Additional error details
     * @return array Error response array
     */
    protected function errorResponse(string $message, int $code = 400, array $additional = []): array
    {
        return [
            'success' => false,
            'status' => $code,
            'error' => $message,
            'details' => $additional,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => uniqid()
        ];
    }
    
    /**
     * Extract data from response
     *
     * @param array $response Full API response
     * @return mixed Data portion of response
     */
    protected function extractData(array $response): mixed
    {
        return $response['data'] ?? $response;
    }
    
    /**
     * Extract pagination metadata from response
     *
     * @param array $response Full API response with meta
     * @ PaginationResult Paginated result object
     */
    protected function extractPagination(array $response): PaginationResult
    {
        if (!isset($response['meta'])) {
            // Try to build pagination from links
            $meta = [
                'current_page' => $response['page'] ?? 1,
                'from' => $response['from'] ?? 1,
                'last_page' => $response->last_page ?? 1,
                'per_page' => $response->per_page ?? 25,
                'to' => $response->to ?? 25,
                'total' => $response->total ?? count($response['data'] ?? 0),
                'links' => $response->links ?? []
            ];
            
            $response['meta'] = $meta;
        }
        
        return PaginationResult::fromResponse($response);
    }
    
    /**
     * Check if response indicates success
     *
     * @param array $response
     * @return bool
     */
    protected function isSuccess(array $response): bool
    {
        return isset($response['success']) && $response['success'] === true
            || (isset($response['status']) && $response['status'] >= 200 && $response['status'] < 300);
    }
    
    /**
     * Get status code from response
     *
     * @param array $response
     * @return int HTTP status code
     */
    protected function getStatusCode(array $response): int
    {
        return (int) ($response['status'] ?? $response['http_code'] ?? 200);
    }
}