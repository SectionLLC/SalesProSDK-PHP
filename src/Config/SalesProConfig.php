<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Config;

/**
 * SalesProConfig — Central configuration for the SalesPro SDK.
 *
 * Copy this file into your CodeIgniter config directory and set
 * values there, or pass an array directly to SalesProClient::create().
 *
 * CodeIgniter 3:  application/config/salespro.php
 * CodeIgniter 4:  app/Config/SalesPro.php  (extend this class)
 */
class SalesProConfig
{
    // ── Connection ────────────────────────────────────────────────────────────

    /**
     * Base URL of your SalesPro instance (trailing slash required).
     * @var string
     */
    public string $baseUrl = 'https://salespro.itechsection.com/';

    // ── OAuth2 Credentials ────────────────────────────────────────────────────

    /**
     * OAuth2 client ID from Connector > API > Clients.
     * @var string
     */
    public string $clientId = '';

    /**
     * OAuth2 client secret from Connector > API > Clients.
     * @var string
     */
    public string $clientSecret = '';

    /**
     * Username for the password-grant flow.
     * @var string
     */
    public string $username = '';

    /**
     * Password for the password-grant flow.
     * @var string
     */
    public string $password = '';

    /**
     * Supply a static bearer token to skip the password-grant flow entirely.
     * Useful for personal access tokens or server-to-server calls.
     * @var string|null
     */
    public ?string $staticAccessToken = null;

    // ── HTTP / Retry ──────────────────────────────────────────────────────────

    /**
     * HTTP request timeout in seconds.
     * @var int
     */
    public int $timeout = 30;

    /**
     * Number of automatic retries on transient failures (5xx, 429, network errors).
     * @var int
     */
    public int $maxRetries = 3;

    /**
     * Base delay in milliseconds between retries (exponential back-off doubles each attempt).
     * @var int
     */
    public int $retryDelayMs = 500;

    /**
     * Whether to verify SSL certificates.
     * Set to false only in local/dev environments.
     * @var bool
     */
    public bool $verifySsl = true;

    // ── Caching ───────────────────────────────────────────────────────────────

    /**
     * Enable in-memory response caching for read-only GET endpoints.
     * @var bool
     */
    public bool $enableCache = false;

    /**
     * Default TTL in seconds for cached responses.
     * @var int
     */
    public int $cacheTtl = 60;

    // ── Logging ───────────────────────────────────────────────────────────────

    /**
     * Enable request/response debug logging.
     * Disable in production — responses may contain sensitive data.
     * @var bool
     */
    public bool $enableLogging = false;

    // ── Multi-Tenant ──────────────────────────────────────────────────────────

    /**
     * Optional tenant identifier forwarded as X-Tenant-Id header.
     * @var string|null
     */
    public ?string $tenantId = null;

    /**
     * Additional HTTP headers appended to every request.
     * @var array<string, string>
     */
    public array $defaultHeaders = [];

    // ── Factory ───────────────────────────────────────────────────────────────

    /**
     * Creates a SalesProConfig from a plain associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $config = new self();
        foreach ($data as $key => $value) {
            // camelCase and snake_case both accepted
            $camel = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if (property_exists($config, $camel)) {
                $config->{$camel} = $value;
            } elseif (property_exists($config, $key)) {
                $config->{$key} = $value;
            }
        }
        return $config;
    }

    /**
     * Validate required fields and throw if anything is missing.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('SalesPro SDK: baseUrl is required.');
        }

        if (empty($this->clientId)) {
            throw new \InvalidArgumentException('SalesPro SDK: clientId is required.');
        }

        if (empty($this->clientSecret)) {
            throw new \InvalidArgumentException('SalesPro SDK: clientSecret is required.');
        }

        if (empty($this->staticAccessToken) && (empty($this->username) || empty($this->password))) {
            throw new \InvalidArgumentException(
                'SalesPro SDK: either staticAccessToken OR (username + password) must be provided.'
            );
        }

        // Ensure trailing slash
        if (!str_ends_with($this->baseUrl, '/')) {
            $this->baseUrl .= '/';
        }
    }
}