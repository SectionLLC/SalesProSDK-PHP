<?php

// ============================================================================
// FILE: src/Services/TableService.php
// ============================================================================

class TableService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** List tables */
    public function list(): array { return $this->get('/connector/api/tables'); }
    
    /** Get table by ID */
    public function find(int $tableId): array { return $this->get("/connector/api/tables/{$tableId}"); }
}