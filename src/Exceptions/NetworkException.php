<?php

// ============================================================================
// FILE: src/Exceptions/NetworkException.php
// ============================================================================

class NetworkException extends ApiException
{
    public function __construct(string $message = 'Network Error', int $code = 0)
    {
        parent::__construct($message, $code);
        $this->setHttpStatus(503);
        $this->setUserMessage('Unable to connect to the server. Please check your network connection.');
    }
}