<?php
/**
 * ============================================================================
 * FILE: src/Services/AuthService.php
 * Authentication & OAuth2 - 15 Endpoints
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Services;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Interfaces\ServiceInterface;
use SalesPro\SDK\Models\AuthToken;
use SalesPro\SDK\Exceptions\AuthenticationException;
use SalesPro\SDK\Exceptions\ValidationException;
use SalesPro\SDK\Traits\RequestTrait;
use SalesPro\SDK\Traits\ResponseTrait;

class AuthService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    
    private SalesPro $client;
    
    public function __construct(SalesPro $client)
    {
        $this->client = $client;
    }
    
    public function getClient(): SalesPro { return $this->client; }
    
    /**
     * Authenticate with username/password
     */
    public function authenticate(string $username, string $password): AuthToken
    {
        $endpoint = '/oauth/token';
        $data = [
            'grant_type' => 'password',
            'client_id' => $this->client->config->client_id,
            'client_secret' => $this->client->config->client_secret,
            'username' => $username,
            'password' => $password
        ];
        
        $response = $this->post($endpoint, $data);
        
        return AuthToken::fromArray($response);
    }
    
    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): AuthToken
    {
        $endpoint = '/oauth/token';
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->client->config->client_id,
            'client_secret' => $this->client->config->client_secret
        ];
        
        $response = $this->post($endpoint, $data);
        
        return AuthToken::fromArray($response);
    }
    
    /**
     * Authorize client application
     */
    public function authorizeClient(string $clientId, string $redirectUri, string $responseType = 'code'): array
    {
        $endpoint = '/oauth/authorize';
        $params = ['client_id' => $clientId, 'redirect_uri' => $redirectUri, 'response_type' => $responseType];
        
        return $this->get($endpoint . '?' . http_build_query($params));
    }
    
    /**
     * Approve authorization request
     */
    public function approveAuthorization(bool $authorize = true): array
    {
        $endpoint = '/oauth/authorize/approve';
        return $this->post($endpoint, ['authorize' => $authorize]);
    }
    
    /**
     * Deny authorization request
     */
    public function denyAuthorization(): array
    {
        $endpoint = '/oauth/authorize/deny';
        return $this->delete($endpoint, 0); // endpoint doesn't need ID
    }
    
    /**
     * List authorized tokens for user
     */
    public function listTokens(): array
    {
        return $this->get('/oauth/tokens');
    }
    
    /**
     * Delete specific token
     */
    public function deleteToken(int $tokenId): bool
    {
        try {
            $this->delete("/oauth/tokens/{$tokenId}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * List OAuth clients
     */
    public function listClients(): array
    {
        return $this->get('/oauth/clients');
    }
    
    /**
     * Create new client
     */
    public function createClient(array $clientData): array
    {
        $required = ['name', 'redirect_uri'];
        foreach ($required as $field) {
            if (empty($clientData[$field])) {
                throw new ValidationException("Missing required field: {$field}");
            }
        }
        
        return $this->post('/oauth/clients', $clientData);
    }
    
    /**
     * Update client
     */
    public function updateClient(int $clientId, array $data): array
    {
        return $this->put("/oauth/clients/{$clientId}", $clientId, $data);
    }
    
    /**
     * Delete client
     */
    public function deleteClient(int $clientId): bool
    {
        try {
            $this->delete("/oauth/clients/{$clientId}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * List available scopes
     */
    public function listScopes(): array
    {
        return $this->get('/oauth/scopes');
    }
    
    /**
     * List Personal Access Tokens (PATs)
     */
    public function listPersonalAccessTokens(): array
    {
        return $this->get('/connector/api/personal-access-tokens');
    }
    
    /**
     * Create Personal Access Token
     */
    public function createPersonalAccessToken(string $name, ?array $scopes = null): array
    {
        $data = ['name' => $name];
        if ($scopes) $data['scopes'] = implode(' ', $scopes);
        
        return $this->post('/connector/api/personal-access-tokens', $data);
    }
    
    /**
     * Delete Personal Access Token
     */
    public function deletePersonalAccessToken(int $token_id): bool
    {
        try {
            $this->delete("/connector/api/personal-access-tokens/{$token_id}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}