<?php

// ============================================================================
// FILE: src/Services/FieldForceService.php
// ============================================================================

class FieldForceService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List visits */
    public function listVisits(array $filters = []): array { return $this->get('/connector/api/visits', $filters); }
    
    /** Create visit */
    public function createVisit(array $data): array { return $this->post('/connector/api/visits', $data); }
    
    /** Update visit status */
    public function updateStatus(int $visitId, string $status): array
    {
        return $this->put("/connector/api/visits/{$visitId}/status", $visitId, ['status' => $status]);
    }
}