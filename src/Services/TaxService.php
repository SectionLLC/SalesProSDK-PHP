<?php

// ============================================================================
// FILE: src/Services/TaxService.php
// ============================================================================

class TaxService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List taxes */
    public function list(): array { return $this->get('/connector/api/taxes'); }
    
    /** Get tax by ID */
    public function find(int $taxId): array { return $this->get("/connector/api/taxes/{$taxId}"); }
}