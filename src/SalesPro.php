<?php
/**
 * ============================================================================
 * SALESPRO PHP SDK - MAIN CLIENT CLASS
 * ============================================================================
 * 
 * The primary entry point for interacting with SalesPro REST API.
 * Supports Laravel 10+ and CodeIgniter 4 frameworks.
 *
 * @package     SalesPro\SDK
 * @version     2.0.0
 * @license     MIT
 * @author      SalesPro Development Team
 */

declare(strict_types=1);

namespace SalesPro\SDK;

use SalesPro\SDK\Config\Configuration;
use SalesPro\SDK\Exceptions\ApiException;
use SalesPro\SDK\Models\AuthToken;
use Psr\Log\LoggerInterface;

/**
 * SalesPro API Client
 * 
 * Main facade class providing access to all API services.
 */
class SalesPro implements ClientInterface
{
    /** @var string SDK Version */
    public const VERSION = '2.0.0';
    
    /** @var Configuration Configuration instance */
    private Configuration $config;
    
    /** @var string|null Current access token */
    private ?string $accessToken = null;
    
    /** @var string|null Refresh token */
    private ?string $refreshToken = null;
    
    /** @var LoggerInterface|null PSR-3 logger */
    private ?LoggerInterface $logger = null;
    
    /** @var array Service instances cache */
    private array $services = [];
    
    /** @var array Request history */
    private array $requestHistory = [];
    
    /** @var array Performance metrics */
    private array $metrics = [
        'total_requests' => 0,
        'total_time' => 0,
        'errors' => 0,
        'cache_hits' => 0,
        'rate_limited' => 0
    ];
    
    /**
     * Create new SalesPro client instance
     *
     * @param array|Configuration $config Configuration array or object
     * @throws \InvalidArgumentException If configuration is invalid
     */
    public function __construct(array|Configuration $config = [])
    {
        if ($config instanceof Configuration) {
            $this->config = $config;
        } elseif (is_array($config)) {
            $this->config = new Configuration($config);
        } else {
            throw new \InvalidArgumentException('Config must be an array or Configuration instance');
        }
        
        $this->validateConfiguration();
        $this->initializeLogger();
    }
    
    /**
     * Validate configuration has required fields
     */
    private function validateConfiguration(): void
    {
        if (empty($this->config->base_url) || empty($this->config->client_id) || empty($this->config->client_secret)) {
            throw new \InvalidArgumentException('Missing required configuration: base_url, client_id, client_secret');
        }
        
        // Validate URL format
        if (!filter_var($this->config->base_url, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $this->config->base_url)) {
            throw new \InvalidArgumentException("Invalid base_url format: {$this->config->base_url}");
        }
        
        // Remove trailing slash
        $this->config->base_url = rtrim($this->config->base_url, '/');
    }
    
    /**
     * Initialize PSR-3 logger
     */
    private function initializeLogger(): void
    {
        try {
            if (class_exists(\Monolog\Logger::class)) {
                $this->logger = new \Monolog\Logger('salespro');
                $this->logger->pushHandler(new \Monolog\Handler\StreamHandler(
                    $this->config->get('logging.path') ?? 'php://stderr',
                    \Monolog\Logger::toMonologLevel($this->config->get('logging.level', 'warning'))
                ));
            } elseif (interface_exists(LoggerInterface::class) && function_exists('app')) {
                $this->logger = app('log');
            }
        } catch (\Throwable $e) {
            $this->logger = null;
        }
    }
    
    /**
     * Set access token
     */
    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;
        return $this;
    }
    
    /**
     * Get current access token
     */
    public function getAccessToken(): ?string { return $this->accessToken; }
    
    /**
     * Set refresh token
     */
    public function setRefreshToken(string $token): self
    {
        $this->refreshToken = $token;
        return $this;
    }
    
    /**
     * Set custom logger
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
    
    /**
     * Enable caching
     */
    public function enableCache(?int $ttl = null): self
    {
        $this->config->set('cache.enabled', true);
        if ($ttl !== null) $this->config->set('cache.ttl', $ttl);
        return $this;
    }
    
    /**
     * Disable caching
     */
    public function disableCache(): self
    {
        $this->config->set('cache.enabled', false);
        return $this;
    }
    
    /**
     * Clear cache
     */
    public function clearCache(?string $key = null): bool
    {
        $prefix = $this->config->get('cache.prefix', 'salespro:');
        if ($key !== null) return cache()->forget($prefix . $key);
        return true; // Framework-specific implementation
    }
    
    // =========================================================================
    // SERVICE ACCESSORS - Lazy-loaded singletons
    // =========================================================================
    
    /** Get Authentication service */
    public function auth(): AuthService { return $this->getService('auth', AuthService::class); }
    
    /** Get Products service */
    public function products(): ProductService { return $this->getService('products', ProductService::class); }
    
    /** Get Sales service */
    public function sales(): SalesService { return $this->getService('sales', SalesService::class); }
    
    /** Get Users service */
    public function users(): UserService { return $this->getService('users', UserService::class); }
    
    /** Get Contacts service */
    public function contacts(): ContactService { return $this->getService('contacts', ContactService::class); }
    
    /** Get Expenses service */
    public function expenses(): ExpenseService { return $this->getService('expenses', ExpenseService::class); }
    
    /** Get Attendance service */
    public function attendance(): AttendanceService { return $this->getService('attendance', AttendanceService::class); }
    
    /** Get Reports service */
    public function reports(): ReportService { return $this->getService('reports', ReportService::class); }
    
    /** Get Locations service */
    public function locations(): LocationService { return $this->getService('locations', LocationService::class); }
    
    /** Get Brands service */
    public function brands(): BrandService { return $this->getService('brands', BrandService::class); }
    
    /** Get Taxes service */
    public function taxes(): TaxService { return $this->getService('taxes', TaxService::class); }
    
    /** Get Units service */
    public function units(): UnitService { return $this->getService('units', UnitService::class); }
    
    /** Get Tables service */
    public function tables(): TableService { return $this->getService('tables', TableService::class); }
    
    /** Get CRM service */
    public function crm(): CrmService { return $this->getService('crm', CrmService::class); }
    
    /** Get Cash Register service */
    public function cashRegister(): CashRegisterService { return $this->getService('cashRegister', CashRegisterService::class); }
    
    /** Get Field Force service */
    public function fieldForce(): FieldForceService { return $this->getService('fieldForce', FieldForceService::class); }
    
    /** Get Module Management service */
    public function modules(): ModuleService { return $this->getService('modules', ModuleService::class); }
    
    /** Get Superadmin service */
    public function superadmin(): SuperadminService { return $this->getService('superadmin', SuperadminService::class); }
    
    /** Get Notifications service */
    public function notifications(): NotificationService { return $this->getService('notifications', NotificationService::class); }
    
    /**
     * Get or create service instance (lazy loading)
     */
    private function getService(string $name, string $class): mixed
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = new $class($this);
        }
        return $this->services[$name];
    }
    
    // =========================================================================
    // CONVENIENCE METHODS
    // =========================================================================
    
    /**
     * Quick authenticate and set token
     */
    public function login(string $username, string $password): AuthToken
    {
        $token = $this->auth()->authenticate($username, $password);
        $this->setAccessToken($token->access_token);
        if ($token->refresh_token) $this->setRefreshToken($token->refresh_token);
        $this->log('info', "User authenticated: {$username}");
        return $token;
    }
    
    /** Logout and clear tokens */
    public function logout(): void
    {
        $this->accessToken = null;
        $this->refreshToken = null;
        $this->clearCache();
        $this->log('info', 'User logged out');
    }
    
    /** Check if authenticated */
    public function isAuthenticated(): bool { return !empty($this->accessToken); }
    
    /**
     * Execute multiple requests concurrently
     */
    public function batch(array $requests): array
    {
        $results = []; $errors = [];
        foreach ($requests as $index => $request) {
            try { $results[$index] = $request(); } catch (ApiException $e) { $errors[$index] = $e; $this->metrics['errors']++; }
        }
        return ['success' => $results, 'errors' => $errors, 'total' => count($requests), 'succeeded' => count($results), 'failed' => count($errors)];
    }
    
    /** Get performance metrics */
    public function getMetrics(): array
    {
        return array_merge($this->metrics, [
            'avg_response_time' => $this->metrics['total_requests'] > 0 ? round($this->metrics['total_time'] / $this->metrics['total_requests'], 2) : 0,
            'error_rate' => $this->metrics['total_requests'] > 0 ? round(($this->metrics['errors'] / $this->metrics['total_requests']) * 100, 2) : 0
        ]);
    }
    
    /** Reset metrics */
    public function resetMetrics(): void { $this->metrics = ['total_requests' => 0, 'total_time' => 0, 'errors' => 0, 'cache_hits' => 0, 'rate_limited' => 0]; }
    
    // =========================================================================
    // LOGGING HELPERS
    // =========================================================================
    
    /** Log message using PSR-3 logger */
    public function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger && $this->config->get('logging.enabled')) {
            $this->logger->$level("[SalesPro] {$message}", $context);
        }
    }
    
    /** Record request timing */
    private function recordTiming(float $duration, string $endpoint): void
    {
        $this->metrics['total_requests']++;
        $this->metrics['total_time'] += $duration;
        $this->requestHistory[] = ['timestamp' => date('Y-m-d H:i:s'), 'endpoint' => $endpoint, 'duration_ms' => round($duration, 2)];
        if (count($this->requestHistory) > 50) array_shift($this->requestHistory);
        $this->log('debug', "API Call: {$endpoint} ({$duration}ms)");
    }
    
    /** Get recent request history */
    public function getRequestHistory(int $limit = 20): array { return array_slice($this->requestHistory, -$limit); }
    
    // =========================================================================
    // MAGIC METHODS
    // =========================================================================
    
    /** Dynamic property access to services: $api->auth instead of $api->auth() */
    public function __get(string $name): mixed
    {
        if (method_exists($this, $name)) return $this->$name();
        throw new \BadMethodCallException("Unknown service or property: {$name}");
    }
    
    /** Convert to string */
    public function __toString(): string { return sprintf("SalesPro SDK v%s [Authenticated: %s] [%s]", self::VERSION, $this->isAuthenticated() ? 'Yes' : 'No', $this->config->base_url); }
    
    /** Debug output */
    public function debug(): array
    {
        return [
            'version' => self::VERSION, 'authenticated' => $this->isAuthenticated(),
            'base_url' => $this->config->base_url, 'has_refresh_token' => !empty($this->refreshToken),
            'services_loaded' => array_keys($this->services), 'metrics' => $this->getMetrics(),
            'recent_requests' => $this->getRequestHistory(5)
        ];
    }
}