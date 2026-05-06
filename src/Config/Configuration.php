<?php
/**
 * ============================================================================
 * SALESPRO PHP SDK - CONFIGURATION CLASS
 * ============================================================================
 * Manages all configuration settings for the SalesPro API client.
 *
 * @package     SalesPro\SDK\Config
 */

declare(strict_types=1);

namespace SalesPro\SDK\Config;
use SalesPro\SDK\SalesPro;

class Configuration
{
    /** @var string Base URL of SalesPro installation */
    public string $base_url = '';
    
    /** @var string OAuth Client ID */
    public string $client_id = '';
    
    /** @var string OAuth Client Secret */
    public string $client_secret = '';
    
    /** @var string API Version (v1, v2, or empty) */
    public string $api_version = '';
    
    /** @var int Request timeout in seconds */
    public int $timeout = 30;
    
    /** @var int Connection timeout in seconds */
    public int $connect_timeout = 10;
    
    /** @var int Number of retry attempts */
    public int $retries = 3;
    
    /** @var int Delay between retries (ms) */
    public int $retry_delay = 1000;
    
    /** @var bool Verify SSL certificates */
    public bool $verify_ssl = true;
    
    /** @var string|null Path to CA bundle */
    public ?string $ca_cert = null;
    
    /** @var string|null Encryption key for tokens */
    public ?string $token_encryption_key = null;
    
    /** @var string|null Proxy URL */
    public ?string $proxy = null;
    
    /** @var bool Keep-alive connections */
    public bool $keepalive = true;
    
    /** @var bool Testing mode flag */
    public bool $testing = false;
    
    /** @var array Custom configuration storage */
    private array $custom = [];
    
    /**
     * Create from array
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $this->custom[$key] = $value;
            }
        }
        $this->loadFromEnvironment();
    }
    
    /**
     * Load from environment variables
     */
    private function loadFromEnvironment(): void
    {
        $envMap = [
            'base_url' => ['SALESPRO_BASE_URL', 'POS_BASE_URL'],
            'client_id' => ['SALESPRO_CLIENT_ID', 'POS_CLIENT_ID'],
            'client_secret' => ['SALESPRO_CLIENT_SECRET', 'POS_CLIENT_SECRET'],
            'api_version' => ['SALESPRO_API_VERSION'],
            'timeout' => ['SALESPRO_TIMEOUT'],
            'verify_ssl' => ['SALESPRO_VERIFY_SSL'],
            'proxy' => ['SALESPRO_PROXY'],
            'token_encryption_key' => ['APP_KEY']
        ];
        
        foreach ($envMap as $property => $envVars) {
            if (empty($this->$property)) {
                foreach ($envVars as $envVar) {
                    $value = getenv($envVar) ?: $_ENV[$envVar] ?? null;
                    if ($value !== null) { $this->$property = $value; break; }
                }
            }
        }
    }
    
    /**
     * Get configuration value (supports dot notation)
     */
    public function get(string $key, $default = null): mixed
    {
        if (property_exists($this, $key)) return $this->$key;
        
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $value = $this->custom;
            foreach ($parts as $part) {
                if (!is_array($value) || !array_key_exists($part, $value)) return $default;
                $value = $value[$part];
            }
            return $value;
        }
        
        return $this->custom[$key] ?? $default;
    }
    
    /**
     * Set configuration value
     */
    public function set(string $key, mixed $value): self
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        } else {
            if (strpos($key, '.') !== false) {
                $parts = explode('.', $key);
                $array = &$this->custom;
                foreach ($parts as $i => $part) {
                    if ($i === count($parts) - 1) { $array[$part] = $value; }
                    else {
                        if (!isset($array[$part]) || !is_array($array[$part])) $array[$part] = [];
                        $array = &$array[$part];
                    }
                }
            } else {
                $this->custom[$key] = $value;
            }
        }
        return $this;
    }
    
    /**
     * Validate configuration
     */
    public function validate(): bool
    {
        if (empty($this->base_url) || empty($this->client_id) || empty($this->client_secret)) {
            throw new \InvalidArgumentException("Missing required: base_url, client_id, client_secret");
        }
        if (!filter_var($this->base_url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid base_url: {$this->base_url}");
        }
        $this->base_url = rtrim($this->base_url, '/');
        return true;
    }
    
    /**
     * Get base API URL with version prefix
     */
    public function getApiBaseUrl(): string
    {
        $url = $this->base_url . (!empty($this->api_version) ? '/connector/api/' . trim($this->api_version, '/') : '/connector/api');
        return $url;
    }
    
    /**
     * Build cURL options
     */
    public function getCurlOptions(): array
    {
        $options = [
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => $this->verify_ssl,
            CURLOPT_SSL_VERIFYHOST => $this->verify_ssl ? 2 : 0,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: SalesPro-PHP-SDK/' . SalesPro::VERSION
            ]
        ];
        
        if ($this->ca_cert && file_exists($this->ca_cert)) $options[CURLOPT_CAINFO] = $this->ca_cert;
        if ($this->proxy) $options[CURLOPT_PROXY] = $this->proxy;
        if ($this->keepalive) $options[CURLOPT_TCP_KEEPALIVE] = 1;
        
        return $options;
    }
}