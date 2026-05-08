<?php

declare(strict_types=1);

namespace ITechSection\SalesPro;

use ITechSection\SalesPro\Auth\AuthManager;
use ITechSection\SalesPro\Config\SalesProConfig;
use ITechSection\SalesPro\Http\ApiClient;
use ITechSection\SalesPro\Services\AttendanceService;
use ITechSection\SalesPro\Services\BrandService;
use ITechSection\SalesPro\Services\BusinessLocationService;
use ITechSection\SalesPro\Services\BusinessService;
use ITechSection\SalesPro\Services\CashRegisterService;
use ITechSection\SalesPro\Services\ContactService;
use ITechSection\SalesPro\Services\CrmService;
use ITechSection\SalesPro\Services\ExpenseService;
use ITechSection\SalesPro\Services\FieldForceService;
use ITechSection\SalesPro\Services\ProductService;
use ITechSection\SalesPro\Services\SalesService;
use ITechSection\SalesPro\Services\SuperadminService;
use ITechSection\SalesPro\Services\TableService;
use ITechSection\SalesPro\Services\TaxService;
use ITechSection\SalesPro\Services\TaxonomyService;
use ITechSection\SalesPro\Services\TypesOfServiceService;
use ITechSection\SalesPro\Services\UnitService;
use ITechSection\SalesPro\Services\UserService;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * SalesProClient — Top-level entry point to the SalesPro SDK.
 *
 * All API endpoint groups are exposed as typed service properties.
 *
 * ── Quick-start ────────────────────────────────────────────────────────────────
 *
 * // From array (great for CodeIgniter config files):
 * $client = SalesProClient::create([
 *     'base_url'      => 'https://salespro.itechsection.com/',
 *     'client_id'     => 'your-client-id',
 *     'client_secret' => 'your-client-secret',
 *     'username'      => 'admin@example.com',
 *     'password'      => 'your-password',
 * ]);
 *
 * // List products
 * $products = $client->products->list();
 * foreach ($products->data as $product) {
 *     echo $product['name'];
 * }
 *
 * // Create a contact
 * $response = $client->contacts->create([
 *     'type'       => 'customer',
 *     'first_name' => 'John',
 *     'last_name'  => 'Doe',
 *     'email'      => 'john@example.com',
 * ]);
 *
 * // Create a sale
 * $sell = $client->sales->create([
 *     'location_id'      => 1,
 *     'contact_id'       => $response->data['id'],
 *     'transaction_date' => date('Y-m-d H:i:s'),
 *     'sell_lines'       => [
 *         ['product_id' => 1, 'quantity' => 2, 'unit_price' => 50.00],
 *     ],
 *     'payments' => [
 *         ['method' => 'cash', 'amount' => 100.00],
 *     ],
 * ]);
 */
class SalesProClient
{
    // ── Service Properties ────────────────────────────────────────────────────

    /** Attendance clock-in, clock-out, and holiday endpoints. */
    public readonly AttendanceService $attendance;

    /** Product brand catalog. */
    public readonly BrandService $brands;

    /** Business location / branch management. */
    public readonly BusinessLocationService $businessLocations;

    /** Business details, P&L reports, notifications, utilities. */
    public readonly BusinessService $business;

    /** POS cash register management. */
    public readonly CashRegisterService $cashRegisters;

    /** Contact (customer / supplier) CRUD and payment recording. */
    public readonly ContactService $contacts;

    /** CRM follow-ups, leads, and call logs. */
    public readonly CrmService $crm;

    /** Expense and refund management. */
    public readonly ExpenseService $expenses;

    /** Field sales visit tracking. */
    public readonly FieldForceService $fieldForce;

    /** Product catalog, variations, stock reports, price groups. */
    public readonly ProductService $products;

    /** Sales transaction lifecycle (sell, return, shipping). */
    public readonly SalesService $sales;

    /** SaaS superadmin — subscription and packages. */
    public readonly SuperadminService $superadmin;

    /** Restaurant / service table management. */
    public readonly TableService $tables;

    /** Tax rate management. */
    public readonly TaxService $taxes;

    /** Product category taxonomy. */
    public readonly TaxonomyService $taxonomies;

    /** Service type management (dine-in, takeaway, delivery…). */
    public readonly TypesOfServiceService $typesOfService;

    /** Units of measurement. */
    public readonly UnitService $units;

    /** User account management and password operations. */
    public readonly UserService $users;

    /** Direct access to the authentication manager. */
    public readonly AuthManager $auth;

    /** Direct access to the raw HTTP client if advanced usage is required. */
    public readonly ApiClient $httpClient;

    // ── Constructor ───────────────────────────────────────────────────────────

    public function __construct(
        SalesProConfig   $config,
        ?AuthManager     $auth       = null,
        ?ApiClient       $httpClient = null,
        ?LoggerInterface $logger     = null,
        ?CacheInterface  $cache      = null
    ) {
        $config->validate();

        $this->auth       = $auth ?? new AuthManager($config, null, $logger);
        $this->httpClient = $httpClient ?? new ApiClient($config, $this->auth, null, $logger, $cache);

        $client = $this->httpClient;

        $this->attendance       = new AttendanceService($client);
        $this->brands           = new BrandService($client);
        $this->businessLocations= new BusinessLocationService($client);
        $this->business         = new BusinessService($client);
        $this->cashRegisters    = new CashRegisterService($client);
        $this->contacts         = new ContactService($client);
        $this->crm              = new CrmService($client);
        $this->expenses         = new ExpenseService($client);
        $this->fieldForce       = new FieldForceService($client);
        $this->products         = new ProductService($client);
        $this->sales            = new SalesService($client);
        $this->superadmin       = new SuperadminService($client);
        $this->tables           = new TableService($client);
        $this->taxes            = new TaxService($client);
        $this->taxonomies       = new TaxonomyService($client);
        $this->typesOfService   = new TypesOfServiceService($client);
        $this->units            = new UnitService($client);
        $this->users            = new UserService($client);
    }

    // ── Named Constructors ────────────────────────────────────────────────────

    /**
     * Create a client from a plain configuration array.
     *
     * @param array<string, mixed> $config
     */
    public static function create(
        array            $config,
        ?LoggerInterface $logger = null,
        ?CacheInterface  $cache  = null
    ): self {
        return new self(SalesProConfig::fromArray($config), null, null, $logger, $cache);
    }

    /**
     * Create a client using a static bearer token (no password-grant flow).
     */
    public static function withToken(
        string           $baseUrl,
        string           $accessToken,
        ?LoggerInterface $logger = null,
        ?CacheInterface  $cache  = null
    ): self {
        return self::create([
            'base_url'             => $baseUrl,
            'client_id'            => 'static',
            'client_secret'        => 'static',
            'static_access_token'  => $accessToken,
        ], $logger, $cache);
    }
}