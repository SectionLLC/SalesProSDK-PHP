<?php
/**
 * ============================================================================
 * FILE: src/Services/UserService.php
 * User Management - 6 Endpoints
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Services;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Interfaces\ServiceInterface;
use SalesPro\SDK\Models\User;
use SalesPro\SDK\Models\PaginationResult;
use SalesPro\SDK\Traits\RequestTrait;
use SalesPro\SDK\Traits\ResponseTrait;

class UserService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /**
     * Get current logged-in user profile
     */
    public function current(): User
    {
        $response = $this->get('/connector/api/user');
        return User::fromArray($response['data'] ?? []);
    }
    
    /**
     * List all users (admin only)
     */
    public function list(array $filters = []): PaginationResult
    {
        return PaginationResult::fromResponse($this->get('/connector/api/users', $filters));
    }
    
    /**
     * Register new user account
     */
    public function register(array $userData): User
    {
        // Validate required fields
        $required = ['first_name', 'last_name', 'email', 'username', 'password'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
        
        $endpoint = '/connector/api/register';
        $response = $this->post($endpoint, $userData);
        
        return User::fromArray($response['data'] ?? []);
    }
    
    /**
     * Get user by ID
     */
    public function find(int $userId): User
    {
        $response = $this->get("/connector/api/users/{$userId}");
        return User::fromArray($response['data'] ?? []);
    }
    
    /**
     * Update user password
     */
    public function updatePassword(string $currentPassword, string $newPassword): bool
    {
        $data = [
            'current_password' => $currentPassword,
            'password' => $newPassword,
            'confirm_password' => $newPassword
        ];
        
        try {
            $this->put('/connector/api/users/update-password', [], $data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Recover forgotten password
     */
    public function recoverPassword(string $usernameOrEmail): array
    {
        return $this->post('/connector/api/recover-password', [
            'username_or_email' => $usernameOrEmail
        ]);
    }
}