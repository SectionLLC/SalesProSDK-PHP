# SalesPro PHP SDK

[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.x-blue)](https://php.net)
[![CI](https://github.com/itechsection/salespro-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/itechsection/salespro-sdk/actions)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Enterprise-grade PHP SDK for the **SalesPro / SectionERP API** with first-class support for **CodeIgniter 3** and **CodeIgniter 4**.

---

## Features V 1.0.0

- ✅ Full coverage of every SalesPro API endpoint
- ✅ OAuth2 password-grant token management with auto-refresh
- ✅ Personal access token support
- ✅ Automatic retry with exponential back-off (via Polly-equivalent Guzzle middleware)
- ✅ Rate-limit handling with `Retry-After` header awareness
- ✅ PSR-16 response caching for read-only endpoints
- ✅ PSR-3 logging support
- ✅ Strongly typed response objects
- ✅ Automatic pagination helper (`getAllPages`)
- ✅ CodeIgniter 3 Library
- ✅ CodeIgniter 4 Service + Config class
- ✅ Multi-tenant support via `X-Tenant-Id` header
- ✅ Full PHPUnit test suite

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | 7.4, 8.0, 8.1, 8.2, 8.3 |
| Guzzle      | ^7.0    |
| ext-curl    | *       |
| ext-json    | *       |

---

## Installation

```bash
composer require itechsection/salesprosdk
```

---

## Quick Start

### Plain PHP

```php
use ITechSection\SalesPro\SalesProClient;

$client = SalesProClient::create([
    'base_url'      => 'https://salespro.itechsection.com/',
    'client_id'     => 'YOUR_CLIENT_ID',
    'client_secret' => 'YOUR_CLIENT_SECRET',
    'username'      => 'admin@yourcompany.com',
    'password'      => 'your-password',
]);

// List products
$products = $client->products->list(['per_page' => 20]);
foreach ($products->data as $product) {
    echo $product['name'] . "\n";
}

// Create a contact
$contact = $client->contacts->create([
    'type'       => 'customer',
    'first_name' => 'John',
    'last_name'  => 'Doe',
    'email'      => 'john@example.com',
]);

// Create a sale
$sell = $client->sales->create([
    'location_id'      => 1,
    'contact_id'       => $contact->data['id'],
    'transaction_date' => date('Y-m-d H:i:s'),
    'sell_lines' => [
        ['product_id' => 1, 'quantity' => 2, 'unit_price' => 50.00],
    ],
    'payments' => [
        ['method' => 'cash', 'amount' => 100.00],
    ],
]);

echo "Invoice: " . $sell->data['invoice_no'];
```

---

## CodeIgniter 3 Integration

### 1. Publish the library and config

```bash
# Copy the library
cp vendor/itechsection/salesprosdk/src/CodeIgniter3/Salespro.php \
   application/libraries/Salespro.php

# Copy the config stub
cp vendor/itechsection/salesprosdk/src/CodeIgniter3/config/salesproconf.php \
   application/config/salespro.php
```

### 2. Edit `application/config/salespro.php`

```php
$config['salespro_base_url']      = 'https://salespro.itechsection.com/';
$config['salespro_client_id']     = 'YOUR_CLIENT_ID';
$config['salespro_client_secret'] = 'YOUR_CLIENT_SECRET';
$config['salespro_username']      = 'admin@yourcompany.com';
$config['salespro_password']      = 'your-password';
```

### 3. Use in your controllers

```php
class Products extends CI_Controller
{
    public function index()
    {
        $this->load->library('salespro');

        $products = $this->salespro->products->list(['per_page' => 20]);
        $this->load->view('products/index', ['products' => $products->data]);
    }

    public function create()
    {
        $this->load->library('salespro');

        try {
            $result = $this->salespro->contacts->create([
                'type'       => 'customer',
                'first_name' => $this->input->post('first_name'),
                'email'      => $this->input->post('email'),
            ]);

            redirect('products');

        } catch (\ITechSection\SalesPro\Exceptions\ValidationException $e) {
            // $e->getErrors() returns ['field' => ['error message', ...]]
            $this->session->set_flashdata('errors', $e->getErrors());
            redirect('products/create');
        }
    }
}
```

---

## CodeIgniter 4 Integration

### 1. Publish config files

```bash
# Copy CI4 config class
cp vendor/itechsection/salesprosdk/src/CodeIgniter4/Config/SalesPro.php \
   app/Config/SalesPro.php

# Copy services registration
cp vendor/itechsection/salesprosdk/src/CodeIgniter4/Config/SalesProServices.php \
   app/Config/SalesProServices.php
```

### 2. Edit `app/Config/SalesPro.php`

```php
public string $baseUrl      = 'https://salespro.itechsection.com/';
public string $clientId     = 'YOUR_CLIENT_ID';
public string $clientSecret = 'YOUR_CLIENT_SECRET';
public string $username     = 'admin@yourcompany.com';
public string $password     = 'your-password';
```

### 3. Use in your controllers

```php
use CodeIgniter\Controller;

class ProductController extends Controller
{
    public function index()
    {
        $client   = \Config\Services::salespro();
        $products = $client->products->list(['per_page' => 20]);

        return view('products/index', ['products' => $products->data]);
    }
}
```

---

## Authentication

### Password Grant (default)

```php
$client = SalesProClient::create([
    'base_url'      => '...',
    'client_id'     => '...',
    'client_secret' => '...',
    'username'      => 'admin@example.com',
    'password'      => 'secret',
]);
```

### Static Token (personal access token)

```php
$client = SalesProClient::withToken(
    'https://salespro.itechsection.com/',
    'your-personal-access-token'
);
```

### Managing Personal Access Tokens

```php
// Create
$tokenData = $client->auth->createPersonalAccessToken('my-app', ['*']);
echo $tokenData['accessToken']; // save this — shown only once

// List
$tokens = $client->auth->listPersonalAccessTokens();

// Delete
$client->auth->deletePersonalAccessToken($tokenData['id']);
```

---

## All Available Services & Methods

| Service | Property | Key Methods |
|---|---|---|
| Attendance | `$client->attendance` | `getAttendance($userId)`, `clockIn($data)`, `clockOut($data)`, `listHolidays($filters)` |
| Brands | `$client->brands` | `list()`, `get($id)` |
| Business Locations | `$client->businessLocations` | `list()`, `get($id)` |
| Business | `$client->business` | `getDetails()`, `getProfitLossReport($filters)`, `getNotifications()`, `getPaymentAccounts()`, `getPaymentMethods()` |
| Cash Registers | `$client->cashRegisters` | `list()`, `create($data)`, `get($id)` |
| Contacts | `$client->contacts` | `list($params)`, `create($data)`, `get($id)`, `update($id, $data)`, `addPayment($data)` |
| CRM | `$client->crm` | `listFollowUps()`, `addFollowUp($data)`, `getFollowUp($id)`, `updateFollowUp($id, $data)`, `listLeads()`, `saveCallLog($data)` |
| Expenses | `$client->expenses` | `list()`, `create($data)`, `get($id)`, `update($id, $data)`, `listRefunds()`, `listCategories()` |
| Field Force | `$client->fieldForce` | `listVisits()`, `createVisit($data)`, `updateVisitStatus($id, $data)` |
| Products | `$client->products` | `list($params)`, `get($id)`, `listVariations($id)`, `listSellingPriceGroups()`, `getStockReport($params)` |
| Sales | `$client->sales` | `list()`, `create($data)`, `get($id)`, `update($id, $data)`, `delete($id)`, `addReturn($data)`, `listReturns()`, `updateShippingStatus($data)` |
| Superadmin | `$client->superadmin` | `getActiveSubscription()`, `getPackages()` |
| Tables | `$client->tables` | `list()`, `get($id)` |
| Taxes | `$client->taxes` | `list()`, `get($id)` |
| Taxonomies | `$client->taxonomies` | `list()`, `get($id)` |
| Types of Service | `$client->typesOfService` | `list()`, `get($id)` |
| Units | `$client->units` | `list()`, `get($id)` |
| Users | `$client->users` | `getLoggedIn()`, `register($data)`, `list()`, `get($id)`, `updatePassword($data)`, `forgotPassword($data)` |

---

## Pagination

Every `list()` method returns an `ApiListResponse` with a `$meta` property:

```php
$result = $client->contacts->list(['page' => 1, 'per_page' => 15]);

echo $result->total();          // total records
echo $result->meta->lastPage;   // last page number
echo $result->meta->currentPage;

if ($result->hasMorePages()) {
    $page2 = $client->contacts->list(['page' => 2, 'per_page' => 15]);
}

// Or fetch ALL pages automatically:
$allContacts = $client->httpClient->getAllPages('connector/api/contactapi');
```

---

## Error Handling

```php
use ITechSection\SalesPro\Exceptions\AuthenticationException;
use ITechSection\SalesPro\Exceptions\AuthorizationException;
use ITechSection\SalesPro\Exceptions\NotFoundException;
use ITechSection\SalesPro\Exceptions\ValidationException;
use ITechSection\SalesPro\Exceptions\RateLimitException;
use ITechSection\SalesPro\Exceptions\ServerException;
use ITechSection\SalesPro\Exceptions\NetworkException;
use ITechSection\SalesPro\Exceptions\SalesProException;

try {
    $result = $client->contacts->create($data);

} catch (ValidationException $e) {
    // Field-level validation failures (HTTP 422)
    foreach ($e->getErrors() as $field => $messages) {
        echo "{$field}: " . implode(', ', $messages) . "\n";
    }

} catch (AuthenticationException $e) {
    // 401 — credentials invalid or token expired
    echo "Auth error: {$e->getMessage()}\n";

} catch (NotFoundException $e) {
    // 404
    echo "Not found: {$e->getMessage()}\n";

} catch (RateLimitException $e) {
    // 429
    echo "Rate limited. Retry after {$e->getRetryAfter()} seconds.\n";

} catch (ServerException $e) {
    // 5xx
    echo "Server error ({$e->getCode()}): {$e->getMessage()}\n";

} catch (NetworkException $e) {
    // DNS / timeout / connection refused
    echo "Network error: {$e->getMessage()}\n";

} catch (SalesProException $e) {
    // Any other SDK error
    echo "SDK error: {$e->getMessage()}\n";
}
```

---

## Configuration Reference

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `base_url` | string | — | SalesPro instance URL (required) |
| `client_id` | string | — | OAuth2 client ID (required) |
| `client_secret` | string | — | OAuth2 client secret (required) |
| `username` | string | — | Login username |
| `password` | string | — | Login password |
| `static_access_token` | string\|null | null | Personal access token (skips password grant) |
| `timeout` | int | 30 | HTTP timeout (seconds) |
| `max_retries` | int | 3 | Retry attempts on transient errors |
| `retry_delay_ms` | int | 500 | Base retry delay (milliseconds, exponential) |
| `verify_ssl` | bool | true | SSL certificate verification |
| `enable_cache` | bool | false | In-memory GET response caching |
| `cache_ttl` | int | 60 | Cache TTL (seconds) |
| `enable_logging` | bool | false | Request/response debug logging |
| `tenant_id` | string\|null | null | Sent as `X-Tenant-Id` header |
| `default_headers` | array | [] | Extra headers on every request |

---

## Running Tests

```bash
composer install
vendor/bin/phpunit
```

---

## How To Use

```bash
composer require itechsection/salesprosdk
```

---

## License

MIT — see [LICENSE](LICENSE).
