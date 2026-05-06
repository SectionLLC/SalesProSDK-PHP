<?php

// ============================================================================
// FILE: src/Exceptions/ServerException.php
// ============================================================================

class ServerException extends ApiException
{
    public function __construct(string $message = 'Server Error', int $code = 500)
    {
        parent::__construct($message, $code);
        $this->setHttpStatus(500);
        $this->setUserMessage('An internal server error occurred');
    }
}