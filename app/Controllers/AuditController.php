<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuditService;

/**
 * Audit Controller
 * 
 * Read-only view of the audit trail.
 * Access restricted to admin and dso roles only.
 */
class AuditController extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Display audit trail (admin/dso only)
     */
    public function index(): void
    {
        $this->requireAuth();

        $role = $this->currentRole();

        // Restrict access to admin and dso only
        if (!in_array($role, ['admin', 'dso'])) {
            http_response_code(403);
            die('<h1>403 Forbidden</h1><p>You do not have access to view audit logs.</p>');
        }

        $logs = $this->auditService->getRecent(200);

        $this->render('audit.index', [
            'logs' => $logs,
        ]);
    }
}
