<?php

// ============================================================================
// FILE: src/Services/UnitService.php
// ============================================================================

class UnitService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List units */
    public function list(): array { return $this->get('/connector/api/units'); }
    
    /** Get unit by ID */
    public function find(int $unitId): array { return $this->get("/connector/api/units/{$unitId}"); }
}