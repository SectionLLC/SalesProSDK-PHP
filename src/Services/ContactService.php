<?php
/**
 * ============================================================================
 * FILE: src/Services/ContactService.php
 * Contact Management - 5 Endpoints
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Services;

use SalesPro\SDK\SalesPro;
use SalesPro\SDK\Interfaces\ServiceInterface;
use SalesPro\SDK\Models\Contact;
use SalesPro\SDK\Models\PaginationResult;
use SalesPro\SDK\Traits\RequestTrait;
use SalesPro\SDK\Traits\ResponseTrait;

class ContactService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /**
     * List contacts with filtering
     */
    public function list(array $filters = []): PaginationResult
    {
        return PaginationResult::fromResponse($this->get('/connector/api/contact', $filters));
    }
    
    /**
     * Create new contact (customer/supplier)
     */
    public function create(array $data): Contact
    {
        // Set default type if not provided
        if (!isset($data['type'])) $data['type'] = 'customer';
        
        $response = $this->post('/connector/api/contact', $data);
        return Contact::fromArray($response['data'] ?? []);
    }
    
    /**
     * Get contact by ID
     */
    public function find(int $contactId): Contact
    {
        $response = $this->get("/connector/api/contact/{$contactId}");
        return Contact::fromArray($response['data'] ?? []);
    }
    
    /**
     * Update contact
     */
    public function update(int $contactId, array $data): Contact
    {
        $this->put("/connector/api/contact/{$contactId}", $contactId, $data);
        return Contact::fromArray($data);
    }
    
    /**
     * Record payment for contact
     */
    public function recordPayment(int $contactId, array $paymentData): array
    {
        $endpoint = '/connector/api/contact/payment';
        $data = array_merge(['contact_id' => $contactId], $paymentData);
        
        return $this->post($endpoint, $data);
    }
}