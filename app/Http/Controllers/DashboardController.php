<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
  /**
   * Display the dashboard.
   */
  public function index(Request $request): Response
  {
    $user = $request->user();
    $currentCompany = $user->currentCompany;

    // If user doesn't have a current company, redirect to company selection
    if (!$currentCompany) {
      return Inertia::render('Companies/Select', [
        'companies' => $user->companies()->with('pivot')->get(),
      ]);
    }

    // Get dashboard data for current company
    $dashboardData = [
      'stats' => [
        'total_billboards' => $currentCompany->billboards()->count(),
        'active_contracts' => $currentCompany->contracts()->where('status', 'active')->count(),
        'monthly_revenue' => $currentCompany->contracts()
          ->where('status', 'active')
          ->whereMonth('created_at', now()->month)
          ->sum('amount'),
        'occupancy_rate' => $this->calculateOccupancyRate($currentCompany),
      ],
      'recent_contracts' => $currentCompany->contracts()
        ->with(['billboard', 'client'])
        ->latest()
        ->limit(5)
        ->get(),
      'top_billboards' => $currentCompany->billboards()
        ->withCount('contracts')
        ->orderBy('contracts_count', 'desc')
        ->limit(5)
        ->get(),
    ];

    return Inertia::render('Dashboard', $dashboardData);
  }

  private function calculateOccupancyRate($company): float
  {
    $totalBillboards = $company->billboards()->count();

    if ($totalBillboards === 0) {
      return 0;
    }

    $occupiedBillboards = $company->billboards()
      ->whereHas('contracts', function ($query) {
        $query->where('status', 'active')
          ->where('start_date', '<=', now())
          ->where('end_date', '>=', now());
      })
      ->count();

    return round(($occupiedBillboards / $totalBillboards) * 100, 2);
  }
}
