<?php

declare(strict_types=1);

/**
 * Salespro — CodeIgniter 3 Library Wrapper for the SalesPro SDK.
 *
 * ── Installation ───────────────────────────────────────────────────────────────
 *
 * 1. Copy this file to:  application/libraries/Salespro.php
 * 2. Copy config stub to: application/config/salespro.php
 * 3. Load in your controller:  $this->load->library('salespro');
 *
 * ── Usage ─────────────────────────────────────────────────────────────────────
 *
 *   $this->salespro->products->list();
 *   $this->salespro->contacts->create([...]);
 *   $this->salespro->sales->create([...]);
 */

use ITechSection\SalesPro\SalesProClient;

class Salespro
{
    /** @var SalesProClient */
    private SalesProClient $sdk;

    /** @var \CI_Controller */
    private object $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('salespro');

        $config = [
            'base_url'      => $this->CI->config->item('salespro_base_url'),
            'client_id'     => $this->CI->config->item('salespro_client_id'),
            'client_secret' => $this->CI->config->item('salespro_client_secret'),
            'username'      => $this->CI->config->item('salespro_username'),
            'password'      => $this->CI->config->item('salespro_password'),
            'timeout'       => $this->CI->config->item('salespro_timeout') ?? 30,
            'max_retries'   => $this->CI->config->item('salespro_max_retries') ?? 3,
            'verify_ssl'    => $this->CI->config->item('salespro_verify_ssl') ?? true,
            'enable_cache'  => $this->CI->config->item('salespro_enable_cache') ?? false,
            'cache_ttl'     => $this->CI->config->item('salespro_cache_ttl') ?? 60,
            'enable_logging'=> $this->CI->config->item('salespro_enable_logging') ?? false,
            'tenant_id'     => $this->CI->config->item('salespro_tenant_id'),
            'static_access_token' => $this->CI->config->item('salespro_static_access_token'),
        ];

        $this->sdk = SalesProClient::create(array_filter($config, fn($v) => $v !== null));

        log_message('debug', 'SalesPro SDK library loaded.');
    }

    /**
     * Magic property accessor so $this->salespro->products works exactly like
     * $client->products on the underlying SDK client.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this->sdk, $name)) {
            return $this->sdk->{$name};
        }

        throw new \RuntimeException("SalesPro: Unknown service '{$name}'.");
    }

    /**
     * Direct access to the underlying SalesProClient for advanced usage.
     */
    public function client(): SalesProClient
    {
        return $this->sdk;
    }
}