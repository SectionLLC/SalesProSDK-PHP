<?php


// ============================================================================
// FILE: src/Exceptions/NotFoundException.php
// ============================================================================

class NotFoundException extends ApiException
{
    public function __construct(string $message = 'Resource Not Found', int $code = 404)
    {
        parent::__construct($message, $code);
        $this->setHttpStatus(404);
        $this->setUserMessage('The requested resource was not found');
    }
}