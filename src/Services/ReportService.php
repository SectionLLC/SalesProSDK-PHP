<?php

// ============================================================================
// FILE: src/Services/ReportService.php
// ============================================================================

class ReportService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** Payment accounts */
    public function paymentAccounts(): array { return $this->get('/connector/api/payment-accounts'); }
    
    /** Payment methods */
    public function paymentMethods(): array { return $this->get('/connector/api/payment-methods'); }
    
    /** Business details */
    public function businessDetails(): array { return $this->get('/connector/api/business-details'); }
    
    /** Profit & Loss report */
    public function profitLoss(?string $startDate = null, ?string $endDate = null, ?int $locationId = null): array
    {
        $params = [];
        if ($startDate) $params['start_date'] = $startDate;
        if ($endDate) $params['end_date'] = $endDate;
        if ($locationId) $params['location_id'] = $locationId;
        return $this->get('/connector/api/profit-loss-report', $params);
    }
    
    /** Product stock report */
    public function productStock(array $filters = []): array { return $this->get('/connector/api/product-stock-report', $filters); }
    
    /** Notifications */
    public function notifications(): array { return $this->get('/connector/api/notifications'); }
    
    /** Location from coordinates */
    public function locationFromCoordinates(float $lat, float $lng): array
    {
        return $this->get('/connector/api/location-from-coordinates', ['lat' => $lat, 'lng' => $lng]);
    }
}