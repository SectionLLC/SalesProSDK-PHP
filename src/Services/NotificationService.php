<?php

// ============================================================================
// FILE: src/Services/NotificationService.php
// ============================================================================

class NotificationService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** Get user notifications */
    public function list(): array { return $this->get('/connector/api/notifications'); }
}