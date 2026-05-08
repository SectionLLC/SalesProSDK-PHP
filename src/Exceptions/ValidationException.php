<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Exceptions;
// ── Validation ────────────────────────────────────────────────────────────────

/**
 * Thrown when the API returns a 422 Unprocessable Entity response.
 */
class ValidationException extends SalesProException
{
    /** @var array<string, string[]> */
    private array $errors;

    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(array $errors, string $message = 'Validation failed.')
    {
        $this->errors = $errors;
        parent::__construct($message, ['errors' => $errors], 422);
    }

    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /** Returns a flat list of all error messages across all fields. */
    public function getMessages(): array
    {
        return array_merge(...array_values($this->errors));
    }
}