<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use ITechSection\SalesPro\Auth\AuthManager;
use ITechSection\SalesPro\Config\SalesProConfig;
use ITechSection\SalesPro\Exceptions\AuthenticationException;
use ITechSection\SalesPro\Exceptions\AuthorizationException;
use ITechSection\SalesPro\Exceptions\NetworkException;
use ITechSection\SalesPro\Exceptions\NotFoundException;
use ITechSection\SalesPro\Exceptions\RateLimitException;
use ITechSection\SalesPro\Exceptions\SalesProException;
use ITechSection\SalesPro\Exceptions\ValidationException;
use ITechSection\SalesPro\Exceptions\ServerException as SalesProServerException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

/**
 * ApiClient — The HTTP transport layer for the SalesPro SDK.
 *
 * Handles:
 *  - Bearer token injection via AuthManager
 *  - Automatic retry with exponential back-off (Polly-equivalent)
 *  - Response parsing and error mapping
 *  - Optional PSR-16 response caching for GET requests
 *  - Pagination helpers
 *  - Debug logging
 */
class ApiClient
{
    private SalesProConfig $config;
    private AuthManager $auth;
    private Client $httpClient;
    private LoggerInterface $logger;
    private ?CacheInterface $cache;

    public function __construct(
        SalesProConfig  $config,
        AuthManager     $auth,
        ?Client         $httpClient = null,
        ?LoggerInterface $logger = null,
        ?CacheInterface  $cache = null
    ) {
        $this->config = $config;
        $this->auth   = $auth;
        $this->logger = $logger ?? new NullLogger();
        $this->cache  = $cache;

        $this->httpClient = $httpClient ?? $this->buildHttpClient();
    }

    // ── Public HTTP Verbs ─────────────────────────────────────────────────────

    /**
     * GET request. Optionally cached.
     *
     * @param  array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = [], bool $cacheable = false): array
    {
        $url = $this->buildUrl($path, $query);
        $cacheKey = '';

        if ($cacheable && $this->cache !== null && $this->config->enableCache) {
            $cacheKey = 'salespro_' . md5($url);
            $cached   = $this->cache->get($cacheKey);
            if ($cached !== null) {
                $this->logger->debug("SalesPro cache hit: {$url}");
                return $cached;
            }
        }

        $response = $this->request('GET', $url);

        if ($cacheable && $this->cache !== null && $this->config->enableCache) {
            $this->cache->set($cacheKey, $response, $this->config->cacheTtl);
        }

        return $response;
    }

    /**
     * POST request with a JSON body.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, ['json' => $data]);
    }

    /**
     * PUT request with a JSON body.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function put(string $path, array $data = []): array
    {
        return $this->request('PUT', $path, ['json' => $data]);
    }

    /**
     * PATCH request with a JSON body.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function patch(string $path, array $data = []): array
    {
        return $this->request('PATCH', $path, ['json' => $data]);
    }

    /**
     * DELETE request.
     *
     * @return array<string, mixed>
     */
    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /**
     * Multipart file upload.
     *
     * @param  array<int, array<string, mixed>> $multipart
     * @return array<string, mixed>
     */
    public function upload(string $path, array $multipart): array
    {
        return $this->request('POST', $path, ['multipart' => $multipart]);
    }

    // ── Pagination Helper ─────────────────────────────────────────────────────

    /**
     * Automatically fetches all pages of a paginated endpoint and merges
     * the data arrays together.
     *
     * @param  array<string, mixed> $query
     * @return array<int, array<string, mixed>>
     */
    public function getAllPages(string $path, array $query = []): array
    {
        $all    = [];
        $page   = 1;
        $query['per_page'] = $query['per_page'] ?? 50;

        do {
            $query['page'] = $page;
            $response = $this->get($path, $query);

            $items = $response['data'] ?? [];
            $all   = array_merge($all, is_array($items) ? $items : []);

            $lastPage = $response['meta']['last_page']
                ?? $response['last_page']
                ?? 1;

            $page++;
        } while ($page <= $lastPage);

        return $all;
    }

    // ── Internal Request ──────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $options = []): array
    {
        $token = $this->auth->getValidToken();

        $options['headers'] = array_merge(
            $this->buildDefaultHeaders($token),
            $options['headers'] ?? []
        );

        if ($this->config->enableLogging) {
            $this->logger->debug("SalesPro → {$method} {$path}", [
                'query' => $options['query'] ?? [],
                'body'  => $options['json'] ?? [],
            ]);
        }

        try {
            $response = $this->httpClient->request($method, $path, $options);
            $body     = (string) $response->getBody();

            if ($this->config->enableLogging) {
                $this->logger->debug("SalesPro ← {$response->getStatusCode()}", [
                    'body' => substr($body, 0, 500),
                ]);
            }

            return json_decode($body, true) ?? [];

        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body       = (string) $e->getResponse()->getBody();
            throw new SalesProServerException($statusCode, $body);
        } catch (ConnectException $e) {
            throw new NetworkException("Connection failed: {$e->getMessage()}", $e);
        } catch (GuzzleException $e) {
            throw new NetworkException("HTTP request failed: {$e->getMessage()}", $e);
        }
    }

    // ── Error Mapping ─────────────────────────────────────────────────────────

    private function handleClientException(ClientException $e): never
    {
        $response   = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body       = (string) $response->getBody();
        $parsed     = json_decode($body, true) ?? [];

        switch ($statusCode) {
            case 401:
                $this->auth->invalidate();
                throw new AuthenticationException(
                    "Unauthorized (401): {$body}",
                    $parsed
                );

            case 403:
                throw new AuthorizationException("Forbidden (403): {$body}");

            case 404:
                throw new NotFoundException("Not found (404): {$body}");

            case 422:
                $errors = $parsed['errors'] ?? ['_' => [$body]];
                throw new ValidationException($errors);

            case 429:
                $retryAfter = (int) ($response->getHeaderLine('Retry-After') ?: 0) ?: null;
                throw new RateLimitException($retryAfter ?: null);

            default:
                throw new SalesProException(
                    "HTTP {$statusCode}: {$body}",
                    $parsed,
                    $statusCode
                );
        }
    }

    // ── Guzzle Client Builder ─────────────────────────────────────────────────

    private function buildHttpClient(): Client
    {
        $stack = HandlerStack::create();
        $stack->push($this->retryMiddleware());

        return new Client([
            'base_uri'        => $this->config->baseUrl,
            'timeout'         => $this->config->timeout,
            'verify'          => $this->config->verifySsl,
            'allow_redirects' => true,
            'handler'         => $stack,
            'http_errors'     => true,
        ]);
    }

    /**
     * Builds a Guzzle middleware that retries on transient failures
     * with exponential back-off.
     */
    private function retryMiddleware(): callable
    {
        $maxRetries   = $this->config->maxRetries;
        $baseDelayMs  = $this->config->retryDelayMs;
        $logger       = $this->logger;

        $decider = function (
            int $retries,
            Request $request,
            ?Response $response,
            ?\Throwable $exception
        ) use ($maxRetries): bool {
            if ($retries >= $maxRetries) {
                return false;
            }

            // Retry on network errors
            if ($exception instanceof ConnectException) {
                return true;
            }

            // Retry on 5xx and 429
            if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504], true)) {
                return true;
            }

            return false;
        };

        $delay = function (int $retries, Response $response) use ($baseDelayMs, $logger): int {
            // Honour Retry-After header for rate limit responses
            $retryAfter = (int) ($response->getHeaderLine('Retry-After') ?: 0);
            if ($retryAfter > 0) {
                $logger->warning("SalesPro: Rate limited. Waiting {$retryAfter}s before retry.");
                return $retryAfter * 1000;
            }

            // Exponential back-off: 500ms, 1000ms, 2000ms, …
            $delay = $baseDelayMs * (2 ** ($retries - 1));
            $logger->warning("SalesPro: Retry #{$retries} after {$delay}ms.");
            return $delay;
        };

        return Middleware::retry($decider, $delay);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildDefaultHeaders(string $bearerToken): array
    {
        $headers = array_merge([
            'Authorization' => "Bearer {$bearerToken}",
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ], $this->config->defaultHeaders);

        if (!empty($this->config->tenantId)) {
            $headers['X-Tenant-Id'] = $this->config->tenantId;
        }

        return $headers;
    }

    private function buildUrl(string $path, array $query): string
    {
        if (empty($query)) {
            return $path;
        }
        // Remove null values before building query string
        $filtered = array_filter($query, fn($v) => $v !== null);
        return $path . '?' . http_build_query($filtered);
    }
}