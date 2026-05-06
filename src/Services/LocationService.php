<?php

// ============================================================================
// FILE: src/Services/LocationService.php
// ============================================================================

class LocationService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List locations */
    public function list(): array { return $this->get('/connector/api/business-locations'); }
    
    /** Get location by ID */
    public function find(int $locationId): array { return $this->get("/connector/api/business-location/{$locationId}"); }
}