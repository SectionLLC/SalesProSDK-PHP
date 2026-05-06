<?php

// ============================================================================
// FILE: src/Exceptions/ValidationException.php
// ============================================================================

class ValidationException extends ApiException
{
    /** @var array Validation errors */
    private array $errors = [];
    
    public function __construct(string $message = 'Validation Failed', int $code = 422, array $errors = [])
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
        $this->setHttpStatus(422);
        $this->setDetails(['validation_errors' => $errors]);
        $this->setUserMessage('The data provided is invalid');
    }
    
    public function getErrors(): array { return $this->errors; }
}