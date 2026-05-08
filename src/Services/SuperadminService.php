<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── Superadmin ────────────────────────────────────────────────────────────────

/**
 * SuperadminService — SaaS subscription and package management.
 *
 * Docs: connector/api/active-subscription
 *       connector/api/packages
 */
class SuperadminService extends AbstractService
{
    public function getActiveSubscription(): ApiResponse
    {
        return $this->getSingle('connector/api/active-subscription');
    }

    public function getPackages(): ApiListResponse
    {
        return $this->getList('connector/api/packages', [], true);
    }
}