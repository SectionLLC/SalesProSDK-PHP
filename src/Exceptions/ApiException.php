<?php
/**
 * ============================================================================
 * FILE: src/Exceptions/ApiException.php
 * Base exception class for all SDK exceptions
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Exceptions;

/**
 * Base API Exception
 */
class ApiException extends \RuntimeException
{
    /** @var string User-friendly error message */
    protected $userMessage = '';
    
    /** @var array Additional details */
    protected $details = [];
    
    /** @var int HTTP status code */
    protected $httpStatus = 500;
    
    /** @var array Response body (if available) */
    protected $response;
    
    /** @var string Request endpoint that failed */
    protected $endpoint = '';
    
    /**
     * Create exception
     */
    public function __construct(string $message = 'API Error', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $message;
    }
    
    /**
     * Set user-friendly message
     */
    public function setUserMessage(string $message): self { $this->userMessage = $message; return $this; }
    
    /** Get user message */
    public function getUserFriendlyMessage(): string { return $this->userMessage; }
    
    /** Set HTTP status code */
    public function setHttpStatus(int $status): self { $this->httpStatus = $status; return $this; }
    
    /** Get HTTP status code */
    public function getHttpStatus(): int { return $this->httpStatus; }
    
    /** Set additional details */
    public function setDetails(array $details): self { $this->details = $details; return $this; }
    
    /** Get details */
    public function getDetails(): array { return $this->details; }
    
    /** Set response data */
    public function setResponse($response): self { $this->response = $response; return $this; }
    
    /** Get response */
    public function getResponse() { return $this->response; }
    
    /** Set endpoint */
    public function setEndpoint(string $endpoint): self { $this->endpoint = $endpoint; return $this; }
    
    /** Get endpoint */
    public function getEndpoint(): string { return $this->endpoint; }
    
    /** Convert to array */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'user_message' => $this->userMessage,
            'code' => $this->getCode(),
            'http_status' => $this->httpStatus,
            'endpoint' => $this->endpoint,
            'details' => $this->details,
            'file' => $this->getFile(),
            'line' => $this->getLine()
        ];
    }
    
    /** Convert to JSON */
    public function toJson(): string { return json_encode($this->toArray()); }
}