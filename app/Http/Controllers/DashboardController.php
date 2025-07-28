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

        // Get comprehensive dashboard data using the service
        $dashboardData = $this->dashboardService->getDashboardData($currentCompany);

        // Transform data using the resource
        $transformedData = new DashboardResource($dashboardData);

        return Inertia::render('Dashboard', [
            'dashboard' => $transformedData,
            'company' => [
                'id' => $currentCompany->id,
                'name' => $currentCompany->name,
                'subscription_plan' => $currentCompany->subscription_plan,
            ],
        ]);
    }
}
