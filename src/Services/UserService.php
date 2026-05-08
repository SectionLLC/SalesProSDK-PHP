<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── User ──────────────────────────────────────────────────────────────────────

/**
 * UserService — User account management, password operations.
 *
 * Docs: connector/api/user
 *       connector/api/user/loggedin
 *       connector/api/user/registration
 *       connector/api/update-password
 *       connector/api/forget-password
 */
class UserService extends AbstractService
{
    public function getLoggedIn(): ApiResponse
    {
        return $this->getSingle('connector/api/user/loggedin');
    }

    /**
     * @param array{
     *   first_name: string,
     *   last_name?: string,
     *   username: string,
     *   email: string,
     *   password: string,
     *   role?: string,
     *   location_id?: int
     * } $data
     */
    public function register(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/user/registration', $data);
    }

    public function list(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/user', $this->compact($params));
    }

    public function get(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/user/{$id}");
    }

    /**
     * @param array{user_id: int, password: string, confirm_password: string} $data
     */
    public function updatePassword(array $data): ActionResponse
    {
        return $this->postAction('connector/api/update-password', $data);
    }

    /**
     * @param array{email: string} $data
     */
    public function forgotPassword(array $data): ActionResponse
    {
        return $this->postAction('connector/api/forget-password', $data);
    }
}