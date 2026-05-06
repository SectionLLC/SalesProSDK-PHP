<?php

// ============================================================================
// FILE: src/Services/SuperadminService.php
// ============================================================================

class SuperadminService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** Get subscription details */
    public function subscriptionDetails(): array { return $this->get('/connector/api/superadmin/subscription'); }
    
    /** Get package list */
    public function packages(): array { return $this->get('/connector/api/superadmin/packages'); }
}