<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Auth;

/**
 * TokenResponse — Immutable value object holding an OAuth2 token set.
 */
final class TokenResponse
{
    public readonly \DateTimeImmutable $expiresAt;

    public function __construct(
        public readonly string  $accessToken,
        public readonly ?string $refreshToken,
        public readonly string  $tokenType,
        public readonly int     $expiresIn,
        ?\DateTimeImmutable     $expiresAt = null
    ) {
        // Buffer 60 seconds so we refresh before the server rejects the token
        $this->expiresAt = $expiresAt
            ?? new \DateTimeImmutable('+' . max(0, $expiresIn - 60) . ' seconds');
    }

    /**
     * Returns true if the token has expired or is about to expire.
     */
    public function isExpired(): bool
    {
        return new \DateTimeImmutable() >= $this->expiresAt;
    }

    /**
     * Returns the Authorization header value ready for use in HTTP requests.
     */
    public function toBearerHeader(): string
    {
        return "{$this->tokenType} {$this->accessToken}";
    }
}