<?php
/**
 * ============================================================================
 * FILE: src/Traits/RequestTrait.php
 * HTTP Request Helpers Trait
 * Provides cURL-based HTTP methods for API calls
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Traits;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Config\Configuration;
use SalesPro\SDK\Exceptions\ApiException;
use SalesPro\Traits\ResponseTrait;

trait RequestTrait
{
    /**
     * Make HTTP GET request
     *
     * @param string $endpoint API endpoint path
     * @param array $params Query parameters
     * @return array Decoded JSON response
     * @throws ApiException On failure
     */
    protected function get(string $endpoint, array $params = []): array
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->executeRequest('GET', $url);
    }
    
    /**
     * Make HTTP POST request
     *
     * @param string $endpoint API endpoint path
     * @param array $data Request body data
     * @return array Decoded JSON response
     * @throws ApiException On failure
     */
    protected function post(string $endpoint, array $data = []): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->executeRequest('POST', $url, $data);
    }
    
    /**
     * Make HTTP PUT request
     *
     * @param string $endpoint API endpoint path
     * @param int $id Resource ID
     * @param array $data Request body data
     * @return array Decoded JSON response
     * @throws ApiException On failure
     */
    protected function put(string $endpoint, int $id, array $data = []): array
    {
        $url = "{$this->getClient()->config->getApiBaseUrl()}{$endpoint}/{$id}";
        return $this->executeRequest('PUT', $url, $data);
    }
    
    /**
     * Make HTTP DELETE request
     *
     * @param string $endpoint API endpoint path
     * @param int $id Resource ID to delete
     * @return bool Success status
     * @throws ApiException On failure
     */
    protected function delete(string $endpoint, int $id): bool
    {
        $url = "{$this->getClient()->config->getApiBaseUrl()}{$endpoint}/{$id}";
        
        try {
            $ch = curl_init();
            curl_setopt_array($ch, $this->getCurlOptions());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_URL, $url);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            }
            
            throw new \RuntimeException("Delete failed with status: {$httpCode}");
        } catch (\Exception $e) {
            throw new ApiException("Delete request failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Execute actual HTTP request via cURL
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $url Full URL with query string
     * @param array|null $data Request body for POST/PUT
     * @return array Decoded JSON response
     * @throws ApiException On failure
     */
    protected function executeRequest(string $method, string $url, ?array $data = null): array
    {
        $client = $this->getClient();
        $config = $client->config;
        
        // Check cache first (for GET requests)
        if ($method === 'GET' && $config->get('cache.enabled')) {
            $cacheKey = $config->get('cache.prefix', 'salespro:') . md5($url);
            $cached = $client->clearCache ? null : cache()->get($cacheKey);
            
            if ($cached !== null) {
                $client->metrics['cache_hits']++;
                $client->log('debug', "Cache HIT: {$url}");
                return json_decode($cached, true);
            }
        }
        
        $startTime = microtime(true);
        
        $ch = curl_init();
        $options = $config->getCurlOptions();
        
        // Set method-specific options
        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                if ($data) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                    $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                }
                break;
                
            case 'PUT':
                $options[URLOPT_CUSTOMREQUEST] = 'PUT';
                if ($data) {
                    $options[URLOPT_POSTFIELDS] = json_encode($data);
                    $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                }
                break;
                
            case 'DELETE':
                $options[URLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
                
            case 'GET':
            default:
                $options[URLOPT_HTTPGET] = true;
                break;
        }
        
        // Set authentication header
        if ($client->isAuthenticated()) {
            $options[CURLOPT_HTTPHEADER][] = "Authorization: Bearer {$client->getAccessToken()}";
        }
        
        // Apply options
        curl_setopt_array($ch, $options);
        
        // Execute request
        $response = curl_exec($ch);
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // ms
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if ($error) {
            throw new NetworkException(
                "cURL Error ({$error}): {$url}",
                0,
                $e
            );
        }
        
        // Record metrics
        $client->recordTiming($duration, $url);
        
        // Parse response
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ServerException(
                "Invalid JSON response from: {$url}",
                0,
                null,
                ['raw_response' => substr($response, 0, 500)]
            );
        }
        
        // Handle errors
        if ($httpCode >= 400) {
            $this->handleError($httpCode, $decoded, $url);
        }
        
        // Cache successful GET responses
        if ($method === 'GET' && $config->get('cache.enabled') && isset($cacheKey)) {
            $ttl = $config->get('cache.ttl', 3600);
            cache()->put($cacheKey, json_encode($decoded), $ttl);
        }
        
        return $decoded;
    }
    
    /**
     * Build URL with query parameters
     *
     * @param string $endpoint Base endpoint
     * @param array $params Query parameters
     * @string Full URL with query string
     */
    protected function buildUrl(string $endpoint, array $params = []): string
    {
        $baseUrl = $this->getClient()->config->getApiBaseUrl();
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $query = http_build_query($params);
            $url .= (strpos($url, '?') !== false ? '&' : '?') . $query;
        }
        
        return $url;
    }
    
    /**
     * Handle API error responses
     */
    private function handleError(int $statusCode, array $response, string $url): void
    {
        $client = $this->getClient();
        $client->metrics['errors']++;
        
        $message = $response['message'] ?? 'API Error';
        $userMessage = $response['error'] ?? $message;
        
        $exceptionClass = match(true) {
            $statusCode == 401 => AuthenticationException::class,
            $statusCode == 404 => NotFoundException::class,
            $statusCode == 422 => ValidationException::class,
            $statusCode == 429 => RateLimitException::class,
            $statusCode >= 500 => ServerException::class,
            default => ApiException::class,
        };
        
        throw new $exception(
            "[{$statusCode}] {$userMessage}: {$url}",
            $statusCode,
            null,
            [
                'status' => $statusCode,
                'endpoint' => $url,
                'response' => $response
            ]
        );
    }
}