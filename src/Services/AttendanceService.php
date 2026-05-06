<?php
// ============================================================================
// FILE: src/Services/AttendanceService.php
// ============================================================================

class AttendanceService implements ServiceInterface
{
    use RequestTrait, ResponseTrait;
    private SalesPro $client;
    
    public function __construct(SalesPro $client) { $this->client = $client; }
    public function getClient(): SalesPro { return $this->client; }
    
    /** Get attendance for user */
    public function get(int $userId): array { return $this->get("/connector/api/get-attendance/{$userId}"); }
    
    /** Clock in */
    public function clockIn(int $userId, ?array $data = []): array { return $this->post('/connector/api/clock-in', array_merge(['user_id' => $userId], ($data ?? [])); }
    
    /** Clock out */
    public function clockOut(int $userId, ?array $data = []): array { return $this->post('/connector/api/clock-out', array_merge(['user_id' => $userId], ($data ?? [])); }
    
    /** List holidays */
    public function holidays(array $filters = []): array { return $this->get('/connector/api/holidays', $filters); }
}