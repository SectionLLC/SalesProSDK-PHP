<?php

// ============================================================================
// FILE: src/Services/CashRegisterService.php
// ============================================================================

class CashRegisterService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List registers */
    public function list(): array { return $this->get('/connector/api/cash-registers'); }
    
    /** Create register */
    public function create(array $data): array { return $this->post('/connector/api/cash-registers', $data); }
    
    /** Get register by ID */
    public function find(int $registerId): array { return $this->get("/connector/api/cash-registers/{$registerId}"); }
}