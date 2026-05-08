<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseService;
use ITechSection\SalesPro\SalesProClient;

/**
 * SalesPro SDK — CodeIgniter 4 Services Registration
 *
 * Merge this snippet into your existing app/Config/Services.php,
 * OR create a new file at app/Config/SalesProServices.php and let
 * CodeIgniter auto-discover it.
 *
 * Usage in a controller:
 *   $client = \Config\Services::salespro();
 *   $products = $client->products->list();
 */
class SalesProServices extends BaseService
{
    /**
     * Returns a shared (singleton) SalesProClient instance.
     *
     * Pass $getShared = false to always get a fresh instance.
     */
    public static function salespro(bool $getShared = true): SalesProClient
    {
        if ($getShared) {
            return static::getSharedInstance('salespro');
        }

        /** @var \Config\SalesPro $cfg */
        $cfg = config('SalesPro');

        return SalesProClient::create([
            'base_url'             => $cfg->baseUrl,
            'client_id'            => $cfg->clientId,
            'client_secret'        => $cfg->clientSecret,
            'username'             => $cfg->username,
            'password'             => $cfg->password,
            'static_access_token'  => $cfg->staticAccessToken,
            'timeout'              => $cfg->timeout,
            'max_retries'          => $cfg->maxRetries,
            'retry_delay_ms'       => $cfg->retryDelayMs,
            'verify_ssl'           => $cfg->verifySsl,
            'enable_cache'         => $cfg->enableCache,
            'cache_ttl'            => $cfg->cacheTtl,
            'enable_logging'       => $cfg->enableLogging,
            'tenant_id'            => $cfg->tenantId,
            'default_headers'      => $cfg->defaultHeaders,
        ]);
    }
}