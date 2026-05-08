<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ITechSection\SalesPro\Config\SalesProConfig;
use ITechSection\SalesPro\Exceptions\AuthenticationException;
use ITechSection\SalesPro\Exceptions\NetworkException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * AuthManager — Handles OAuth2 password-grant token acquisition,
 * in-process caching, and automatic token refresh for the SalesPro API.
 *
 * Usage:
 *   $auth  = new AuthManager($config);
 *   $token = $auth->getValidToken();   // auto-fetches or refreshes
 */
class AuthManager
{
    private SalesProConfig $config;
    private Client $httpClient;
    private LoggerInterface $logger;

    private ?TokenResponse $currentToken = null;

    public function __construct(
        SalesProConfig $config,
        ?Client $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->config     = $config;
        $this->logger     = $logger ?? new NullLogger();
        $this->httpClient = $httpClient ?? new Client([
            'base_uri'        => $config->baseUrl,
            'timeout'         => $config->timeout,
            'verify'          => $config->verifySsl,
            'allow_redirects' => true,
        ]);
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Returns a valid bearer token, fetching or refreshing as necessary.
     *
     * @throws AuthenticationException
     * @throws NetworkException
     */
    public function getValidToken(): string
    {
        // Static token bypasses all OAuth flows
        if (!empty($this->config->staticAccessToken)) {
            return $this->config->staticAccessToken;
        }

        // Cached token still valid
        if ($this->currentToken !== null && !$this->currentToken->isExpired()) {
            return $this->currentToken->accessToken;
        }

        // Try refresh if we have a refresh token
        if ($this->currentToken?->refreshToken !== null) {
            try {
                $this->currentToken = $this->refreshToken($this->currentToken->refreshToken);
                return $this->currentToken->accessToken;
            } catch (AuthenticationException $e) {
                $this->logger->warning('SalesPro: Token refresh failed, re-authenticating.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Full password-grant request
        $this->currentToken = $this->requestToken(
            $this->config->username,
            $this->config->password
        );

        return $this->currentToken->accessToken;
    }

    /**
     * Request a new token via password grant.
     *
     * @throws AuthenticationException
     * @throws NetworkException
     */
    public function requestToken(string $username, string $password): TokenResponse
    {
        $this->logger->info('SalesPro: Requesting new access token.', ['username' => $username]);

        $payload = [
            'grant_type'    => 'password',
            'client_id'     => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'username'      => $username,
            'password'      => $password,
            'scope'         => '',
        ];

        return $this->sendTokenRequest($payload);
    }

    /**
     * Refresh an existing token.
     *
     * @throws AuthenticationException
     * @throws NetworkException
     */
    public function refreshToken(string $refreshToken): TokenResponse
    {
        $this->logger->info('SalesPro: Refreshing access token.');

        $payload = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'refresh_token' => $refreshToken,
            'scope'         => '',
        ];

        return $this->sendTokenRequest($payload);
    }

    /**
     * Invalidate the locally cached token, forcing re-acquisition on next call.
     */
    public function invalidate(): void
    {
        $this->currentToken = null;
    }

    /**
     * Create a personal access token (requires the user to be authenticated).
     *
     * @param string[] $scopes
     * @return array<string, mixed>
     * @throws AuthenticationException
     * @throws NetworkException
     */
    public function createPersonalAccessToken(string $name, array $scopes = []): array
    {
        $token = $this->getValidToken();

        try {
            $response = $this->httpClient->post('oauth/personal-access-tokens', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept'        => 'application/json',
                ],
                'json' => ['name' => $name, 'scopes' => $scopes],
            ]);

            return json_decode((string) $response->getBody(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new NetworkException('Failed to create personal access token.', $e);
        }
    }

    /**
     * List all personal access tokens for the current user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listPersonalAccessTokens(): array
    {
        $token = $this->getValidToken();

        try {
            $response = $this->httpClient->get('oauth/personal-access-tokens', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept'        => 'application/json',
                ],
            ]);

            return json_decode((string) $response->getBody(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new NetworkException('Failed to list personal access tokens.', $e);
        }
    }

    /**
     * Delete a personal access token by its ID.
     */
    public function deletePersonalAccessToken(string $tokenId): void
    {
        $token = $this->getValidToken();

        try {
            $this->httpClient->delete("oauth/personal-access-tokens/{$tokenId}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept'        => 'application/json',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new NetworkException('Failed to delete personal access token.', $e);
        }
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function sendTokenRequest(array $payload): TokenResponse
    {
        try {
            $response = $this->httpClient->post('oauth/token', [
                'form_params' => $payload,
                'headers'     => ['Accept' => 'application/json'],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (empty($body['access_token'])) {
                throw new AuthenticationException('Token response missing access_token.');
            }

            $token = new TokenResponse(
                accessToken:  $body['access_token'],
                refreshToken: $body['refresh_token'] ?? null,
                tokenType:    $body['token_type'] ?? 'Bearer',
                expiresIn:    (int) ($body['expires_in'] ?? 3600),
            );

            $this->logger->info('SalesPro: Token acquired.', [
                'expires_at' => $token->expiresAt->format(\DateTime::ATOM),
            ]);

            return $token;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = (string) $e->getResponse()->getBody();
            throw new AuthenticationException(
                "Token request failed ({$e->getResponse()->getStatusCode()}): {$body}"
            );
        } catch (GuzzleException $e) {
            throw new NetworkException('Network error during token request.', $e);
        }
    }
}