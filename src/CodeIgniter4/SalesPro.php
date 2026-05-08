<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * SalesPro SDK — CodeIgniter 4 Config Class
 *
 * Place this file at: app/Config/SalesPro.php
 *
 * Access in your code:
 *   $config = config('SalesPro');
 *   or inject via DI:
 *   $client = \Config\Services::salespro();
 */
class SalesPro extends BaseConfig
{
    // ── Connection ────────────────────────────────────────────────────────────

    public string $baseUrl = 'https://salespro.itechsection.com/';

    // ── OAuth2 Credentials ────────────────────────────────────────────────────

    public string $clientId     = '';
    public string $clientSecret = '';
    public string $username     = '';
    public string $password     = '';

    /** Use instead of password grant when you have a personal access token. */
    public ?string $staticAccessToken = null;

    // ── HTTP / Retry ──────────────────────────────────────────────────────────

    public int  $timeout    = 30;
    public int  $maxRetries = 3;
    public int  $retryDelayMs = 500;
    public bool $verifySsl  = true;

    // ── Caching ───────────────────────────────────────────────────────────────

    public bool $enableCache = false;
    public int  $cacheTtl   = 60;

    // ── Logging ───────────────────────────────────────────────────────────────

    public bool $enableLogging = false;

    // ── Multi-Tenant ──────────────────────────────────────────────────────────

    public ?string $tenantId = null;

    /** @var array<string, string> */
    public array $defaultHeaders = [];
}