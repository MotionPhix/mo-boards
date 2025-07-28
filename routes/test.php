<?php

use App\Models\Company;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Route;

Route::get('/test-dashboard', function () {
    $company = Company::first();

    if (!$company) {
        return response()->json(['error' => 'No company found. Please run the seeder first.']);
    }

    $service = new DashboardService();
    $data = $service->getDashboardData($company);

    return response()->json([
        'message' => 'Dashboard service working correctly!',
        'company' => $company->name,
        'sections' => array_keys($data),
        'stats' => $data['stats'],
    ]);
});
