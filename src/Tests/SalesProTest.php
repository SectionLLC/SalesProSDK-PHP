<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ITechSection\SalesPro\Auth\AuthManager;
use ITechSection\SalesPro\Auth\TokenResponse;
use ITechSection\SalesPro\Config\SalesProConfig;
use ITechSection\SalesPro\Exceptions\AuthenticationException;
use ITechSection\SalesPro\Exceptions\NotFoundException;
use ITechSection\SalesPro\Exceptions\RateLimitException;
use ITechSection\SalesPro\Exceptions\ValidationException;
use ITechSection\SalesPro\Http\ApiClient;
use ITechSection\SalesPro\SalesProClient;
use PHPUnit\Framework\TestCase;

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Builds a Guzzle mock stack from a list of Response objects.
 *
 * @param Response[] $responses
 */
function mockClient(array $responses): Client
{
    $mock    = new MockHandler($responses);
    $handler = HandlerStack::create($mock);
    return new Client(['handler' => $handler]);
}

function jsonResponse(mixed $data, int $status = 200): Response
{
    return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
}

function config(array $overrides = []): SalesProConfig
{
    return SalesProConfig::fromArray(array_merge([
        'base_url'      => 'https://salespro.itechsection.com/',
        'client_id'     => 'test-client',
        'client_secret' => 'test-secret',
        'username'      => 'admin@test.com',
        'password'      => 'password',
    ], $overrides));
}

// ── TokenResponse Tests ───────────────────────────────────────────────────────

class TokenResponseTest extends TestCase
{
    public function test_not_expired_when_fresh(): void
    {
        $token = new TokenResponse('tok', null, 'Bearer', 3600);
        $this->assertFalse($token->isExpired());
    }

    public function test_expired_when_past_expiry(): void
    {
        $past  = new \DateTimeImmutable('-1 second');
        $token = new TokenResponse('tok', null, 'Bearer', 0, $past);
        $this->assertTrue($token->isExpired());
    }

    public function test_bearer_header_format(): void
    {
        $token = new TokenResponse('abc123', null, 'Bearer', 3600);
        $this->assertSame('Bearer abc123', $token->toBearerHeader());
    }
}

// ── SalesProConfig Tests ──────────────────────────────────────────────────────

class SalesProConfigTest extends TestCase
{
    public function test_from_array_maps_snake_case(): void
    {
        $cfg = SalesProConfig::fromArray([
            'base_url'      => 'https://example.com/',
            'client_id'     => 'cid',
            'client_secret' => 'sec',
            'username'      => 'u',
            'password'      => 'p',
        ]);

        $this->assertSame('https://example.com/', $cfg->baseUrl);
        $this->assertSame('cid', $cfg->clientId);
    }

    public function test_validate_adds_trailing_slash(): void
    {
        $cfg = config(['base_url' => 'https://example.com']);
        $cfg->validate();
        $this->assertStringEndsWith('/', $cfg->baseUrl);
    }

    public function test_validate_throws_when_client_id_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $cfg = config(['client_id' => '']);
        $cfg->validate();
    }

    public function test_validate_throws_when_no_auth_provided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $cfg = config(['username' => '', 'password' => '']);
        $cfg->validate();
    }
}

// ── AuthManager Tests ─────────────────────────────────────────────────────────

class AuthManagerTest extends TestCase
{
    public function test_request_token_returns_token_response(): void
    {
        $http = mockClient([
            jsonResponse([
                'access_token'  => 'tok123',
                'refresh_token' => 'ref456',
                'token_type'    => 'Bearer',
                'expires_in'    => 31536000,
            ]),
        ]);

        $auth = new AuthManager(config(), $http);
        $tok  = $auth->requestToken('admin@test.com', 'password');

        $this->assertSame('tok123', $tok->accessToken);
        $this->assertSame('ref456', $tok->refreshToken);
        $this->assertFalse($tok->isExpired());
    }

    public function test_request_token_throws_on_401(): void
    {
        $this->expectException(AuthenticationException::class);

        $http = mockClient([
            jsonResponse(['error' => 'invalid_grant'], 401),
        ]);

        $auth = new AuthManager(config(), $http);
        $auth->requestToken('bad@user.com', 'wrong');
    }

    public function test_get_valid_token_returns_static_token(): void
    {
        $cfg  = config(['static_access_token' => 'my-static-token']);
        $auth = new AuthManager($cfg);

        $this->assertSame('my-static-token', $auth->getValidToken());
    }

    public function test_get_valid_token_reuses_cached_token(): void
    {
        $tokenJson = [
            'access_token' => 'cached',
            'token_type'   => 'Bearer',
            'expires_in'   => 31536000,
        ];

        // Only one token response queued — second call must use the cache
        $http = mockClient([jsonResponse($tokenJson)]);
        $auth = new AuthManager(config(), $http);

        $first  = $auth->getValidToken();
        $second = $auth->getValidToken();

        $this->assertSame('cached', $first);
        $this->assertSame('cached', $second);
    }
}

// ── ApiClient Tests ───────────────────────────────────────────────────────────

class ApiClientTest extends TestCase
{
    private function makeAuthManager(string $token = 'test-token'): AuthManager
    {
        $cfg     = config(['static_access_token' => $token]);
        return new AuthManager($cfg);
    }

    private function makeClient(array $responses, ?AuthManager $auth = null): ApiClient
    {
        $http = mockClient($responses);
        $cfg  = config(['static_access_token' => 'test-token']);
        return new ApiClient($cfg, $auth ?? $this->makeAuthManager(), $http);
    }

    public function test_get_returns_decoded_json(): void
    {
        $client = $this->makeClient([
            jsonResponse(['data' => [['id' => 1, 'name' => 'Widget']]]),
        ]);

        $result = $client->get('connector/api/product');
        $this->assertSame(1, $result['data'][0]['id']);
    }

    public function test_post_sends_json_body(): void
    {
        $client = $this->makeClient([
            jsonResponse(['data' => ['id' => 42], 'success' => true]),
        ]);

        $result = $client->post('connector/api/contactapi', ['first_name' => 'Jane']);
        $this->assertSame(42, $result['data']['id']);
    }

    public function test_get_throws_not_found_on_404(): void
    {
        $this->expectException(NotFoundException::class);

        $client = $this->makeClient([
            jsonResponse(['message' => 'Not found'], 404),
        ]);

        $client->get('connector/api/product/99999');
    }

    public function test_get_throws_validation_exception_on_422(): void
    {
        $this->expectException(ValidationException::class);

        $client = $this->makeClient([
            jsonResponse(['errors' => ['first_name' => ['The first name field is required.']]], 422),
        ]);

        $client->post('connector/api/contactapi', []);
    }

    public function test_get_throws_rate_limit_exception_on_429(): void
    {
        $this->expectException(RateLimitException::class);

        $response = new Response(429, ['Retry-After' => '30'], json_encode(['message' => 'Too many requests']));
        $client   = $this->makeClient([$response]);

        $client->get('connector/api/product');
    }

    public function test_delete_returns_action_response(): void
    {
        $client = $this->makeClient([
            jsonResponse(['success' => true, 'msg' => 'Sale deleted.']),
        ]);

        $result = $client->delete('connector/api/sell/1');
        $this->assertTrue($result['success']);
    }
}

// ── Service Integration Tests ─────────────────────────────────────────────────

class ContactServiceTest extends TestCase
{
    private function makeSDK(array $responses): SalesProClient
    {
        $http    = mockClient($responses);
        $cfg     = config(['static_access_token' => 'tok']);
        $auth    = new AuthManager($cfg);
        $apiCli  = new ApiClient($cfg, $auth, $http);

        return new SalesProClient($cfg, $auth, $apiCli);
    }

    public function test_list_contacts_returns_list_response(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse([
                'data' => [
                    ['id' => 1, 'name' => 'John Doe', 'type' => 'customer'],
                    ['id' => 2, 'name' => 'ACME Corp', 'type' => 'supplier'],
                ],
                'meta' => ['current_page' => 1, 'total' => 2, 'per_page' => 15, 'last_page' => 1],
            ]),
        ]);

        $result = $sdk->contacts->list();

        $this->assertCount(2, $result->data);
        $this->assertSame(2, $result->total());
        $this->assertFalse($result->hasMorePages());
    }

    public function test_create_contact_returns_api_response(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse([
                'data'    => ['id' => 99, 'name' => 'Jane Smith'],
                'success' => true,
            ]),
        ]);

        $result = $sdk->contacts->create([
            'type'       => 'customer',
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
        ]);

        $this->assertTrue($result->success);
        $this->assertSame(99, $result->data['id']);
    }

    public function test_update_contact(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse(['data' => ['id' => 1, 'mobile' => '0501234567'], 'success' => true]),
        ]);

        $result = $sdk->contacts->update(1, ['mobile' => '0501234567']);
        $this->assertTrue($result->success);
    }
}

class SalesServiceTest extends TestCase
{
    private function makeSDK(array $responses): SalesProClient
    {
        $http   = mockClient($responses);
        $cfg    = config(['static_access_token' => 'tok']);
        $auth   = new AuthManager($cfg);
        $apiCli = new ApiClient($cfg, $auth, $http);
        return new SalesProClient($cfg, $auth, $apiCli);
    }

    public function test_create_sell_returns_response(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse([
                'data'    => ['id' => 10, 'invoice_no' => 'SI-0001', 'final_total' => 100.00],
                'success' => true,
            ]),
        ]);

        $result = $sdk->sales->create([
            'location_id'      => 1,
            'contact_id'       => 1,
            'transaction_date' => '2025-01-01 10:00:00',
            'sell_lines'       => [['product_id' => 1, 'quantity' => 1, 'unit_price' => 100.00]],
            'payments'         => [['method' => 'cash', 'amount' => 100.00]],
        ]);

        $this->assertTrue($result->success);
        $this->assertSame('SI-0001', $result->data['invoice_no']);
    }

    public function test_delete_sell_returns_action_response(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse(['success' => true, 'msg' => 'Transaction deleted successfully.']),
        ]);

        $result = $sdk->sales->delete(10);
        $this->assertTrue($result->success);
    }

    public function test_list_returns_returns_paginated_response(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse([
                'data' => [['id' => 5, 'invoice_no' => 'SR-0001']],
                'meta' => ['current_page' => 1, 'total' => 1, 'per_page' => 15, 'last_page' => 1],
            ]),
        ]);

        $result = $sdk->sales->listReturns();
        $this->assertCount(1, $result->data);
    }
}

class ProductServiceTest extends TestCase
{
    private function makeSDK(array $responses): SalesProClient
    {
        $http   = mockClient($responses);
        $cfg    = config(['static_access_token' => 'tok']);
        $auth   = new AuthManager($cfg);
        $apiCli = new ApiClient($cfg, $auth, $http);
        return new SalesProClient($cfg, $auth, $apiCli);
    }

    public function test_list_products(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse([
                'data' => [['id' => 1, 'name' => 'Cola 330ml', 'sku' => 'COL-001']],
                'meta' => ['current_page' => 1, 'total' => 1, 'per_page' => 15, 'last_page' => 1],
            ]),
        ]);

        $result = $sdk->products->list();
        $this->assertSame('Cola 330ml', $result->data[0]['name']);
    }

    public function test_get_product_by_id(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse(['data' => ['id' => 1, 'name' => 'Cola 330ml']]),
        ]);

        $result = $sdk->products->get(1);
        $this->assertSame(1, $result->data['id']);
    }
}

class AttendanceServiceTest extends TestCase
{
    private function makeSDK(array $responses): SalesProClient
    {
        $http   = mockClient($responses);
        $cfg    = config(['static_access_token' => 'tok']);
        $auth   = new AuthManager($cfg);
        $apiCli = new ApiClient($cfg, $auth, $http);
        return new SalesProClient($cfg, $auth, $apiCli);
    }

    public function test_clock_in(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse(['success' => true, 'msg' => 'Clocked in successfully.']),
        ]);

        $result = $sdk->attendance->clockIn(['user_id' => 1]);
        $this->assertTrue($result->success);
    }

    public function test_list_holidays(): void
    {
        $sdk = $this->makeSDK([
            jsonResponse([
                'data' => [['id' => 1, 'name' => 'New Year', 'start_date' => '2025-01-01']],
            ]),
        ]);

        $result = $sdk->attendance->listHolidays(['start_date' => '2025-01-01', 'end_date' => '2025-12-31']);
        $this->assertCount(1, $result->data);
    }
}