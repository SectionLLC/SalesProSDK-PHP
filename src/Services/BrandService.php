<?php

// ============================================================================
// FILE: src/Services/BrandService.php
// ============================================================================

class BrandService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List brands */
    public function list(): array { return $this->get('/connector/api/list-brands'); }
    
    /** Get brand by ID */
    public function find(int $brandId): array { return $this->get("/connector/api/get-brand/{$brandId}"); }
}