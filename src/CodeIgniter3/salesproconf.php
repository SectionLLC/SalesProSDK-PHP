<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| SalesPro SDK — CodeIgniter 3 Configuration
|--------------------------------------------------------------------------
|
| Copy this file to: application/config/salespro.php
|
| You can also set these values from environment variables:
|   $config['salespro_client_id'] = getenv('SALESPRO_CLIENT_ID');
|
*/

// ── Connection ────────────────────────────────────────────────────────────────

$config['salespro_base_url'] = 'https://salespro.itechsection.com/';

// ── OAuth2 Credentials ────────────────────────────────────────────────────────
// Obtain these from: SalesPro Admin → Connector → API → Clients

$config['salespro_client_id']     = '';
$config['salespro_client_secret'] = '';
$config['salespro_username']      = '';
$config['salespro_password']      = '';

// Optional: use a static personal access token instead of the password grant
$config['salespro_static_access_token'] = null;

// ── HTTP / Retry ──────────────────────────────────────────────────────────────

$config['salespro_timeout']     = 30;   // seconds
$config['salespro_max_retries'] = 3;
$config['salespro_verify_ssl']  = true; // set false for local dev only

// ── Caching ───────────────────────────────────────────────────────────────────

$config['salespro_enable_cache'] = false;
$config['salespro_cache_ttl']    = 60;  // seconds

// ── Logging ───────────────────────────────────────────────────────────────────

$config['salespro_enable_logging'] = false; // true for debug; disable in production

// ── Multi-Tenant ──────────────────────────────────────────────────────────────

$config['salespro_tenant_id'] = null; // set to your tenant ID for multi-tenant instances, or null to auto-detect