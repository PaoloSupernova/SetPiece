<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Complaint;
use App\Services\ToxicityService;
use App\Services\AuditService;
use App\Services\DeadlockService;

/**
 * Complaint Controller
 * 
 * Handles CRUD operations for complaints with safeguarding enforcement,
 * toxicity analysis, and deadlock escalation.
 */
class ComplaintController extends Controller
{
    private Complaint $complaintModel;
    private ToxicityService $toxicityService;
    private AuditService $auditService;
    private DeadlockService $deadlockService;

    public function __construct()
    {
        $this->complaintModel = new Complaint();
        $this->toxicityService = new ToxicityService();
        $this->auditService = new AuditService();
        $this->deadlockService = new DeadlockService();
    }

    /**
     * List all complaints with breach indicators
     */
    public function index(): void
    {
        $this->requireAuth();

        $role = $this->currentRole();
        $complaints = $this->complaintModel->allForRole($role);

        // Get breached complaint IDs
        $breachedComplaints = $this->complaintModel->breached($role);
        $breachedIds = array_column($breachedComplaints, 'id');

        $this->render('complaints.index', [
            'complaints' => $complaints,
            'breachedIds' => $breachedIds,
        ]);
    }

    /**
     * Show the create complaint form
     */
    public function create(): void
    {
        $this->requireAuth();

        $this->render('complaints.create', [
            'errors' => $_SESSION['errors'] ?? [],
        ]);

        unset($_SESSION['errors']);
    }

    /**
     * Store a new complaint
     */
    public function store(): void
    {
        $this->requireAuth();

        $role = $this->currentRole();
        $userId = $this->currentUserId();

        // Validate input
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $stadiumBlock = trim($_POST['stadium_block'] ?? 'unknown');

        $errors = [];

        if (empty($subject)) {
            $errors[] = 'Subject is required';
        } elseif (strlen($subject) > 500) {
            $errors[] = 'Subject must not exceed 500 characters';
        }

        if (empty($body)) {
            $errors[] = 'Description is required';
        }

        // CRITICAL: Enforce SLO cannot create safeguarding tickets
        if ($role === 'slo' && $category === 'safeguarding') {
            $errors[] = 'SLO users cannot create safeguarding complaints';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->withOldInput($_POST);
            $this->redirect('/complaints/create');
        }

        // Run toxicity analysis
        $combinedText = $subject . ' ' . $body;
        $toxicityScore = $this->toxicityService->analyse($combinedText);

        // Create complaint
        $complaintId = $this->complaintModel->create([
            'user_id' => $userId,
            'subject' => $subject,
            'body' => $body,
            'category' => $category,
            'stadium_block' => $stadiumBlock,
            'toxicity_score' => $toxicityScore,
            'status' => 'new',
        ]);

        // Log to audit trail
        $this->auditService->log(
            $complaintId,
            $userId,
            'complaint_created',
            null,
            'new'
        );

        // Flash toxicity warning if needed
        if ($this->toxicityService->isToxic($toxicityScore)) {
            $percentage = round($toxicityScore * 100);
            $this->flash(
                'warning',
                "High toxicity detected ({$percentage}%). This complaint has been flagged for review."
            );
        } else {
            $this->flash('success', 'Complaint created successfully');
        }

        $this->clearOldInput();
        $this->redirect('/complaints/' . $complaintId);
    }

    /**
     * Show a single complaint with breach check
     */
    public function show(string $id): void
    {
        $this->requireAuth();

        $complaintId = (int)$id;
        $role = $this->currentRole();

        $complaint = $this->complaintModel->findForRole($complaintId, $role);

        if (!$complaint) {
            $this->render('complaints.not_found', [], 'app');
            return;
        }

        // Check if breached
        $isBreached = false;
        $daysOverdue = 0;
        
        $deadlineTime = strtotime($complaint['deadlock_deadline']);
        $now = time();
        
        if ($deadlineTime < $now && !in_array($complaint['status'], ['resolved', 'deadlock'])) {
            $isBreached = true;
            $daysOverdue = floor(($now - $deadlineTime) / 86400);
        }

        // Get messages
        $messages = $this->complaintModel->getMessages($complaintId);

        // Get audit logs
        $auditLogs = $this->auditService->getForComplaint($complaintId);

        $this->render('complaints.show', [
            'complaint' => $complaint,
            'isBreached' => $isBreached,
            'daysOverdue' => $daysOverdue,
            'messages' => $messages,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Escalate a complaint to deadlock and generate PDF letter
     */
    public function escalate(string $id): void
    {
        $this->requireAuth();

        $complaintId = (int)$id;
        $role = $this->currentRole();
        $userId = $this->currentUserId();

        $complaint = $this->complaintModel->findForRole($complaintId, $role);

        if (!$complaint) {
            $this->flash('error', 'Complaint not found');
            $this->redirect('/complaints');
        }

        // Check if already resolved or escalated
        if (in_array($complaint['status'], ['resolved', 'deadlock'])) {
            $this->flash('error', 'Cannot escalate a ' . $complaint['status'] . ' complaint');
            $this->redirect('/complaints/' . $complaintId);
        }

        // Update status to deadlock
        $previousStatus = $complaint['status'];
        $this->complaintModel->updateStatus($complaintId, 'deadlock');

        // Log to audit trail
        $this->auditService->log(
            $complaintId,
            $userId,
            'escalated_to_deadlock',
            $previousStatus,
            'deadlock'
        );

        // Generate and stream PDF deadlock letter
        $reference = $this->deadlockService->formatReference($complaintId);
        $filename = "deadlock_{$reference}.pdf";

        $letterData = [
            'reference' => $reference,
            'date' => date('d F Y'),
            'supporter_name' => $complaint['user_name'] ?? 'Supporter',
            'complaint_subject' => $complaint['subject'],
            'complaint_body' => substr($complaint['body'], 0, 500) . '...',
            'created_date' => date('d F Y', strtotime($complaint['created_at'])),
            'deadline_date' => date('d F Y', strtotime($complaint['deadlock_deadline'])),
        ];

        $this->deadlockService->streamLetter($letterData, $filename);
    }
}
