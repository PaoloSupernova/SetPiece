<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Complaint Model
 * 
 * CRITICAL SAFEGUARDING SILO:
 * All query methods accept a $role parameter. When $role === 'slo',
 * the safeguarding category is filtered out at the SQL query level.
 * This is a GDPR requirement enforced in the database query builder.
 */
class Complaint
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all complaints for a specific role with safeguarding filtering
     * 
     * @param string $role User role (admin, slo, dso, steward)
     * @return array List of complaints
     */
    public function allForRole(string $role): array
    {
        $sql = "SELECT 
                    c.*,
                    u.name as user_name,
                    u.email as user_email
                FROM complaints c
                LEFT JOIN users u ON c.user_id = u.id";
        
        // CRITICAL: Apply safeguarding silo for SLO role
        if ($role === 'slo') {
            $sql .= " WHERE c.category != 'safeguarding'";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Find a single complaint by ID with role-based filtering
     * 
     * @param int $id Complaint ID
     * @param string $role User role
     * @return array|false Complaint data or false if not found/forbidden
     */
    public function findForRole(int $id, string $role): array|false
    {
        $sql = "SELECT 
                    c.*,
                    u.name as user_name,
                    u.email as user_email
                FROM complaints c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = ?";
        
        // CRITICAL: Apply safeguarding silo for SLO role
        if ($role === 'slo') {
            $sql .= " AND c.category != 'safeguarding'";
        }
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Get deadlock watchlist (complaints nearing deadline)
     * 
     * @param string $role User role
     * @param int $warningDays Days before deadline to show warning
     * @return array List of complaints approaching deadline
     */
    public function deadlockWatchlist(string $role, int $warningDays = 7): array
    {
        $sql = "SELECT 
                    c.*,
                    u.name as user_name,
                    DATEDIFF(c.deadlock_deadline, NOW()) as days_remaining
                FROM complaints c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.deadlock_deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
                AND c.status NOT IN ('resolved', 'deadlock')";
        
        // CRITICAL: Apply safeguarding silo for SLO role
        if ($role === 'slo') {
            $sql .= " AND c.category != 'safeguarding'";
        }
        
        $sql .= " ORDER BY c.deadlock_deadline ASC";
        
        return $this->db->fetchAll($sql, [$warningDays]);
    }

    /**
     * Get breached complaints (past deadline and still open)
     * 
     * @param string $role User role
     * @return array List of breached complaints
     */
    public function breached(string $role): array
    {
        $sql = "SELECT 
                    c.*,
                    u.name as user_name,
                    DATEDIFF(NOW(), c.deadlock_deadline) as days_overdue
                FROM complaints c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.deadlock_deadline < NOW()
                AND c.status NOT IN ('resolved', 'deadlock')";
        
        // CRITICAL: Apply safeguarding silo for SLO role
        if ($role === 'slo') {
            $sql .= " AND c.category != 'safeguarding'";
        }
        
        $sql .= " ORDER BY c.deadlock_deadline ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Count complaints by stadium block for heatmap
     * 
     * @param string $role User role
     * @return array Associative array of block => count
     */
    public function countByBlock(string $role): array
    {
        $sql = "SELECT 
                    stadium_block,
                    COUNT(*) as count
                FROM complaints
                WHERE status NOT IN ('resolved', 'deadlock')";
        
        // CRITICAL: Apply safeguarding silo for SLO role
        if ($role === 'slo') {
            $sql .= " AND category != 'safeguarding'";
        }
        
        $sql .= " GROUP BY stadium_block";
        
        $results = $this->db->fetchAll($sql);
        
        // Convert to associative array
        $counts = [
            'north' => 0,
            'south' => 0,
            'east' => 0,
            'west' => 0,
            'unknown' => 0,
        ];
        
        foreach ($results as $row) {
            $counts[$row['stadium_block']] = (int)$row['count'];
        }
        
        return $counts;
    }

    /**
     * Create a new complaint with 42-day IFO deadline
     * 
     * @param array $data Complaint data
     * @return int New complaint ID
     */
    public function create(array $data): int
    {
        $deadlockDays = (int)($_ENV['DEADLOCK_DAYS'] ?? 42);
        
        $sql = "INSERT INTO complaints 
                (user_id, subject, body, status, category, toxicity_score, 
                 deadlock_deadline, stadium_block, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), ?, NOW())";
        
        $this->db->query($sql, [
            $data['user_id'],
            $data['subject'],
            $data['body'],
            $data['status'] ?? 'new',
            $data['category'] ?? 'general',
            $data['toxicity_score'] ?? null,
            $deadlockDays,
            $data['stadium_block'] ?? 'unknown',
        ]);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update complaint status
     * 
     * @param int $id Complaint ID
     * @param string $newStatus New status value
     * @return bool Success status
     */
    public function updateStatus(int $id, string $newStatus): bool
    {
        $sql = "UPDATE complaints SET status = ?, updated_at = NOW() WHERE id = ?";
        
        try {
            $this->db->query($sql, [$newStatus, $id]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update complaint status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get complaint messages
     * 
     * @param int $complaintId Complaint ID
     * @return array List of messages
     */
    public function getMessages(int $complaintId): array
    {
        $sql = "SELECT * FROM complaint_messages 
                WHERE complaint_id = ? 
                ORDER BY created_at ASC";
        
        return $this->db->fetchAll($sql, [$complaintId]);
    }

    /**
     * Add a message to a complaint
     * 
     * @param int $complaintId Complaint ID
     * @param string $senderType sender type (supporter, staff, system)
     * @param string $body Message body
     * @return int Message ID
     */
    public function addMessage(int $complaintId, string $senderType, string $body): int
    {
        $sql = "INSERT INTO complaint_messages 
                (complaint_id, sender_type, body, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        $this->db->query($sql, [$complaintId, $senderType, $body]);
        
        return (int)$this->db->lastInsertId();
    }
}
