<?php

declare(strict_types=1);

namespace ITechSection\SalesPro\Services;

use ITechSection\SalesPro\Http\ActionResponse;
use ITechSection\SalesPro\Http\ApiListResponse;
use ITechSection\SalesPro\Http\ApiResponse;

// ── Attendance ────────────────────────────────────────────────────────────────

/**
 * AttendanceService — Clock-in, clock-out, and holiday endpoints.
 *
 * Docs: connector/api/get-attendance
 *       connector/api/clock-in
 *       connector/api/clock-out
 *       connector/api/holidays
 */
class AttendanceService extends AbstractService
{
    /**
     * Get the most recent attendance record for a user.
     */
    public function getAttendance(int $userId): ApiResponse
    {
        return $this->getSingle("connector/api/get-attendance/{$userId}");
    }

    /**
     * Record a clock-in event.
     *
     * @param array{
     *   user_id: int,
     *   clock_in_time?: string,
     *   clock_in_note?: string,
     *   ip_address?: string,
     *   latitude?: string,
     *   longitude?: string
     * } $data
     */
    public function clockIn(array $data): ActionResponse
    {
        return $this->postAction('connector/api/clock-in', $data);
    }

    /**
     * Record a clock-out event.
     *
     * @param array{
     *   user_id: int,
     *   clock_out_time?: string,
     *   clock_out_note?: string,
     *   latitude?: string,
     *   longitude?: string
     * } $data
     */
    public function clockOut(array $data): ActionResponse
    {
        return $this->postAction('connector/api/clock-out', $data);
    }

    /**
     * List holidays filtered by date range and/or location.
     *
     * @param array{start_date?: string, end_date?: string, location_id?: int} $filters
     */
    public function listHolidays(array $filters = []): ApiListResponse
    {
        return $this->getList('connector/api/holidays', $this->compact($filters), true);
    }
}
