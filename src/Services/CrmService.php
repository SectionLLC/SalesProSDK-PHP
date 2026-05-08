<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;
// ── CRM ───────────────────────────────────────────────────────────────────────

/**
 * CrmService — Follow-ups, leads, and call logs.
 *
 * Docs: connector/api/crm-follow-ups
 *       connector/api/crm-leads
 *       connector/api/crm-call-logs
 *       connector/api/crm-follow-up-resources
 */
class CrmService extends AbstractService
{
    // Follow-ups

    public function listFollowUps(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/crm-follow-ups', $this->compact($params));
    }

    /**
     * @param array{
     *   contact_id?: int,
     *   lead_id?: int,
     *   follow_up_date: string,
     *   follow_up_type?: string,
     *   note?: string,
     *   assigned_to?: int
     * } $data
     */
    public function addFollowUp(array $data): ApiResponse
    {
        return $this->postSingle('connector/api/crm-follow-ups', $data);
    }

    public function getFollowUp(int $id): ApiResponse
    {
        return $this->getSingle("connector/api/crm-follow-ups/{$id}");
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFollowUp(int $id, array $data): ApiResponse
    {
        return $this->putSingle("connector/api/crm-follow-ups/{$id}", $data);
    }

    public function getFollowUpResources(): ApiResponse
    {
        return $this->getSingle('connector/api/crm-follow-up-resources', [], true);
    }

    // Leads

    public function listLeads(array $params = []): ApiListResponse
    {
        return $this->getList('connector/api/crm-leads', $this->compact($params));
    }

    // Call Logs

    /**
     * @param array{
     *   contact_id: int,
     *   duration?: string,
     *   note?: string,
     *   result?: string
     * } $data
     */
    public function saveCallLog(array $data): ActionResponse
    {
        return $this->postAction('connector/api/crm-call-logs', $data);
    }
}