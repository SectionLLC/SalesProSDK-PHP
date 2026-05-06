<?php

// ============================================================================
// FILE: src/Exceptions/RateLimitException.php
// ============================================================================

class RateLimitException extends ApiException
{
    /** @var int Seconds to wait before retrying */
    private int $retryAfter = 60;
    
    public function __construct(string $message = 'Rate Limit Exceeded', int $code = 429)
    {
        parent::__construct($message, $code);
        $this->setHttpStatus(429);
        $this->setUserMessage('Too many requests. Please wait and try again.');
    }
    
    public function setRetryAfter(int $seconds): self { $this->retryAfter = $seconds; return $this; }
    public function getRetryAfter(): int { return $this->retryAfter; }
}