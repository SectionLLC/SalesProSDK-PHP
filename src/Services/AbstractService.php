<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiClient;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;

/**
 * AbstractService — Base class for all SalesPro endpoint services.
 *
 * Provides typed helper wrappers around the raw ApiClient verbs,
 * standardising how responses are mapped to value objects.
 */
abstract class AbstractService
{
    protected ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    // ── Typed Helpers ─────────────────────────────────────────────────────────

    /**
     * Sends a GET and wraps the result in an ApiListResponse.
     *
     * @param array<string, mixed> $query
     */
    protected function getList(
        string $path,
        array  $query = [],
        bool   $cacheable = false
    ): ApiListResponse {
        $raw = $this->client->get($path, $query, $cacheable);
        return ApiListResponse::fromArray($raw);
    }

    /**
     * Sends a GET and wraps the result in an ApiResponse.
     *
     * @param array<string, mixed> $query
     */
    protected function getSingle(
        string $path,
        array  $query = [],
        bool   $cacheable = false
    ): ApiResponse {
        $raw = $this->client->get($path, $query, $cacheable);
        return ApiResponse::fromArray($raw);
    }

    /**
     * Sends a POST and wraps the result in an ApiResponse.
     *
     * @param array<string, mixed> $data
     */
    protected function postSingle(string $path, array $data = []): ApiResponse
    {
        $raw = $this->client->post($path, $data);
        return ApiResponse::fromArray($raw);
    }

    /**
     * Sends a POST and wraps the result in an ActionResponse.
     *
     * @param array<string, mixed> $data
     */
    protected function postAction(string $path, array $data = []): ActionResponse
    {
        $raw = $this->client->post($path, $data);
        return ActionResponse::fromArray($raw);
    }

    /**
     * Sends a PUT and wraps the result in an ApiResponse.
     *
     * @param array<string, mixed> $data
     */
    protected function putSingle(string $path, array $data = []): ApiResponse
    {
        $raw = $this->client->put($path, $data);
        return ApiResponse::fromArray($raw);
    }

    /**
     * Sends a DELETE and wraps the result in an ActionResponse.
     */
    protected function deleteSingle(string $path): ActionResponse
    {
        $raw = $this->client->delete($path);
        return ActionResponse::fromArray($raw);
    }

    /**
     * Strips null values from an array before sending as query/body params.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    protected function compact(array $params): array
    {
        return array_filter($params, fn($v) => $v !== null);
    }
}