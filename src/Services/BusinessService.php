<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Business ──────────────────────────────────────────────────────────────────

/**
 * BusinessService — Business details, reports, notifications, utilities.
 *
 * Docs: connector/api/business-details
 *       connector/api/profit-loss-report
 *       connector/api/notifications
 *       connector/api/get-location
 *       connector/api/payment-accounts
 *       connector/api/payment-methods
 */
class BusinessService extends AbstractService
{
    public function getDetails(): ApiResponse
    {
        return $this->getSingle('connector/api/business-details', [], true);
    }

    /**
     * @param array{start_date?: string, end_date?: string, location_id?: int} $filters
     */
    public function getProfitLossReport(array $filters = []): ApiResponse
    {
        return $this->getSingle('connector/api/profit-loss-report', $this->compact($filters));
    }

    public function getNotifications(): ApiListResponse
    {
        return $this->getList('connector/api/notifications');
    }

    public function getLocationFromCoordinates(string $latitude, string $longitude): ApiResponse
    {
        return $this->getSingle('connector/api/get-location', compact('latitude', 'longitude'));
    }

    public function getPaymentAccounts(): ApiListResponse
    {
        return $this->getList('connector/api/payment-accounts', [], true);
    }

    public function getPaymentMethods(): ApiListResponse
    {
        return $this->getList('connector/api/payment-methods', [], true);
    }
}