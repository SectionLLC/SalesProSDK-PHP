<?php

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your UltimatePOS installation.
    | Do not include trailing slash.
    |
    */
    'base_url' => env('SALESPRO_BASE_URL', 'https://salespro.itechsection.com'),
    
    /*
    |--------------------------------------------------------------------------
    | OAuth Credentials
    |--------------------------------------------------------------------------
    |
    | These are obtained from Connector > Clients in your UltimatePOS admin.
    |
    */
    'client_id' => env('SALESPRO_CLIENT_ID'),
    'client_secret' => env('SALESPRO_CLIENT_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Use 'v1' for newer endpoints or leave empty for legacy.
    |
    */
    'api_version' => env('SALESPRO_API_VERSION', ''),
    
    /*
    |--------------------------------------------------------------------------
    | HTTP Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => env('SALESPRO_TIMEOUT', 30),
    'connect_timeout' => 10,
    'retries' => env('SALESPRO_RETRIES', 3),
    'retry_delay' => 1000, // milliseconds
    
    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'verify_ssl' => env('SALESPRO_VERIFY_SSL', true),
    'token_encryption_key' => env('APP_KEY'), // Laravel's APP_KEY
    
    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Enable to cache GET requests. Uses framework's cache driver.
    |
    */
    'cache' => [
        'enabled' => env('SALESPRO_CACHE_ENABLED', false),
        'ttl' => env('SALESPRO_CACHE_TTL', 3600), // seconds
        'prefix' => 'salespro:',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('SALESPRO_LOG_ENABLED', false),
        'level' => env('SALESPRO_LOG_LEVEL', 'warning'),
        'log_requests' => true,
        'log_responses' => true,
        'max_body_size' => 10240, // bytes to log
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Proxy (for corporate networks)
    |--------------------------------------------------------------------------
    */
    'proxy' => env('SALESPRO_PROXY'), // tcp://proxy.example.com:8080
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure how the SDK handles rate limits from the server.
    |
    */
    'rate_limiting' => [
        'enabled' => true,
        'max_retries' => 3,
        'retry_after_header' => 'Retry-After',
        'backoff_multiplier' => 2,
    ],
];