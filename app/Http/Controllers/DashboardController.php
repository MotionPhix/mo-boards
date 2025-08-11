<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentCompany = $user->currentCompany;

        // If user doesn't have a current company, redirect to company selection
        if (!$currentCompany) {
            return Inertia::render('companies/Select', [
                'companies' => $user->companies()->with('pivot')->get(),
            ]);
        }

        // Get user role in current company
        $userRole = $user->getRoleInCompany($currentCompany);

        // Get user abilities for this company
        $canViewFinancial = $user->hasCompanyPermission($currentCompany, 'finance.view_revenue') ||
                           $user->hasCompanyPermission($currentCompany, 'contracts.view_financial') ||
                           $user->hasRole(['super_admin', 'company_owner']) ||
                           $userRole === 'company_owner';

        $canViewAdvancedAnalytics = $user->hasCompanyPermission($currentCompany, 'analytics.view_advanced') ||
                                   $user->hasRole(['super_admin', 'company_owner', 'manager']) ||
                                   in_array($userRole, ['company_owner', 'manager']);

        // Get comprehensive dashboard data using the service with role context
        $dashboardData = $this->dashboardService->getDashboardData(
            $currentCompany,
            $user,
            [
                'can_view_financial' => $canViewFinancial,
                'can_view_advanced_analytics' => $canViewAdvancedAnalytics,
                'user_role' => $userRole
            ]
        );

        // Transform data using the resource
        $transformedData = new DashboardResource($dashboardData);

        // Determine which dashboard component to render based on user role
        $dashboardComponent = match ($userRole) {
            'super_admin' => 'dashboard/SuperAdmin',
            'company_owner' => 'dashboard/CompanyOwner',
            'manager' => 'dashboard/Manager',
            'editor' => 'dashboard/Editor',
            'viewer' => 'dashboard/Viewer',
            default => 'dashboard/Viewer', // Fallback to viewer
        };

        return Inertia::render($dashboardComponent, [
            'dashboard' => $transformedData,
            'company' => [
                'id' => $currentCompany->id,
                'name' => $currentCompany->name,
                'subscription_plan' => $currentCompany->subscription_plan,
            ],
            'user_context' => [
                'role' => $userRole,
                'can_view_financial' => $canViewFinancial,
                'can_view_advanced_analytics' => $canViewAdvancedAnalytics,
            ],
        ]);
    }
}
