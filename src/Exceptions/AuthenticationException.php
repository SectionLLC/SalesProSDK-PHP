<?php


// ============================================================================
// FILE: src/Exceptions/AuthenticationException.php
// ============================================================================

class AuthenticationException extends ApiException
{
    public function __construct(string $message = 'Authentication Failed', int $code = 401)
    {
        parent::__construct($message, $code);
        $this->setHttpStatus(401);
        $this->setUserMessage('Invalid or expired authentication credentials');
    }
}