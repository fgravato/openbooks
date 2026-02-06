<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TenantManager;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ResponseFactory $inertia,
        private readonly TenantManager $tenantManager,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $organization = $this->tenantManager->getCurrentOrganization();

        return $this->inertia->render('Dashboard', [
            'summary' => [
                'open_invoices' => 0,
                'overdue_invoices' => 0,
                'monthly_revenue_cents' => 0,
                'active_clients' => 0,
            ],
            'activity' => [
                [
                    'id' => 1,
                    'label' => __('Welcome to OpenBooks. Your account is ready.'),
                    'timestamp' => now()->toIso8601String(),
                ],
                [
                    'id' => 2,
                    'label' => __('Complete your billing profile to unlock invoicing workflows.'),
                    'timestamp' => now()->subMinute()->toIso8601String(),
                ],
            ],
            'context' => [
                'user_name' => $user?->name,
                'organization_name' => $organization?->name,
            ],
        ]);
    }
}
