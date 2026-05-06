<?php

// ============================================================================
// FILE: src/Services/CrmService.php
// ============================================================================

class CrmService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List follow-ups */
    public function listFollowUps(array $filters = []): PaginationResult { return PaginationResult::fromResponse($this->get('/connector/api/follow-ups', $filters)); }
    
    /** Add follow-up */
    public function addFollowUp(array $data): array { return $this->post('/connector/api/follow-ups', $data); }
    
    /** Get follow-up by ID */
    public function getFollowUp(int $id): array { return $this->get("/connector/api/follow-ups/{$id}"); }
    
    /** Update follow-up */
    public function updateFollowUp(int $id, array $data): array { $this->put("/connector/api/follow-ups/{$id}", $id, $data); return $data; }
    
    /** Get resources */
    public function getResources(): array { return $this->get('/connector/api/follow-up-resources'); }
    
    /** List leads */
    public function listLeads(array $filters = []): array { return $this->get('/connector/api/leads', $filters); }
    
    /** Save call log */
    public function saveCallLog(array $data): array { return $this->post('/connector/api/contact/call-log', $data); }
}