<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Audit Trail Service
 * 
 * Provides append-only audit logging for all complaint state changes.
 * The audit_logs table is immutable via database triggers.
 */
class AuditService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log an audit entry
     * 
     * @param int $complaintId Complaint ID
     * @param int|null $userId User ID performing the action (null for system)
     * @param string $action Description of the action
     * @param string|null $previousState Previous state value
     * @param string|null $newState New state value
     * @return bool Success status
     */
    public function log(
        int $complaintId,
        ?int $userId,
        string $action,
        ?string $previousState = null,
        ?string $newState = null
    ): bool {
        try {
            $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
            $now = $driver === 'sqlite' ? "datetime('now')" : "NOW()";
            
            $sql = "INSERT INTO audit_logs 
                    (complaint_id, user_id, action, previous_state, new_state, timestamp) 
                    VALUES (?, ?, ?, ?, ?, {$now})";
            
            $this->db->query($sql, [
                $complaintId,
                $userId,
                $action,
                $previousState,
                $newState
            ]);

            return true;
        } catch (\Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent audit logs (for admin/dso view)
     * 
     * @param int $limit Number of records to fetch
     * @return array Array of audit log entries
     */
    public function getRecent(int $limit = 200): array
    {
        $sql = "SELECT 
                    al.*,
                    u.name as user_name,
                    u.email as user_email,
                    c.subject as complaint_subject
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN complaints c ON al.complaint_id = c.id
                ORDER BY al.timestamp DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Get audit logs for a specific complaint
     * 
     * @param int $complaintId Complaint ID
     * @return array Array of audit log entries
     */
    public function getForComplaint(int $complaintId): array
    {
        $sql = "SELECT 
                    al.*,
                    u.name as user_name,
                    u.email as user_email
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.complaint_id = ?
                ORDER BY al.timestamp ASC";
        
        return $this->db->fetchAll($sql, [$complaintId]);
    }
}
