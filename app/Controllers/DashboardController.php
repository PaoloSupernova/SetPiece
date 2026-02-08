<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Complaint;

/**
 * Dashboard Controller
 * 
 * Displays the main dashboard with breach alerts, watchlist, and heatmap.
 */
class DashboardController extends Controller
{
    private Complaint $complaintModel;

    public function __construct()
    {
        $this->complaintModel = new Complaint();
    }

    /**
     * Show the dashboard
     */
    public function index(): void
    {
        $this->requireAuth();

        $role = $this->currentRole();
        $warningDays = (int)($_ENV['DEADLOCK_WARNING_DAYS'] ?? 7);

        // Fetch dashboard data
        $breached = $this->complaintModel->breached($role);
        $watchlist = $this->complaintModel->deadlockWatchlist($role, $warningDays);
        $blockCounts = $this->complaintModel->countByBlock($role);

        $this->render('dashboard', [
            'breached' => $breached,
            'watchlist' => $watchlist,
            'blockCounts' => $blockCounts,
            'warningDays' => $warningDays,
        ]);
    }
}
