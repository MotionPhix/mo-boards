<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'ensure.company.access'])->group(function () {
  Route::get(
    '/dashboard',
    [\App\Http\Controllers\DashboardController::class, 'index']
  )->name('dashboard');

  // Company routes
  Route::resource(
    'companies',
    \App\Http\Controllers\CompanyController::class
  );

  Route::post(
    '/companies/{company}/switch',
    [\App\Http\Controllers\CompanyController::class, 'switchCompany']
  )->name('companies.switch');

  // Billboard routes
  Route::resource(
    'billboards',
    \App\Http\Controllers\BillboardController::class
  );

  // Additional billboard routes
  Route::post(
    '/billboards/{billboard}/duplicate',
    [\App\Http\Controllers\BillboardController::class, 'duplicate']
  )->name('billboards.duplicate');

  Route::post(
    '/billboards/bulk-update',
    [\App\Http\Controllers\BillboardController::class, 'bulkUpdate']
  )->name('billboards.bulk-update');

  Route::get(
    '/billboards/search',
    [\App\Http\Controllers\BillboardController::class, 'search']
  )->name('billboards.search');

  Route::get(
    '/billboards/export',
    [\App\Http\Controllers\BillboardController::class, 'export']
  )->name('billboards.export');

  // Team routes
  Route::resource(
    'team',
    \App\Http\Controllers\TeamController::class
  )->except(['show']);

  Route::post(
    '/team/invite',
    [\App\Http\Controllers\TeamController::class, 'invite']
  )->name('team.invite');

  // Profile routes
  Route::get(
    '/profile',
    [\App\Http\Controllers\ProfileController::class, 'edit']
  )->name('profile.edit');

  Route::patch(
    '/profile',
    [\App\Http\Controllers\ProfileController::class, 'update']
  )->name('profile.update');

  Route::delete(
    '/profile',
    [\App\Http\Controllers\ProfileController::class, 'destroy']
  )->name('profile.destroy');

  Route::resource(
    'contract-templates',
    \App\Http\Controllers\ContractTemplateController::class
  );

  // Contract routes
  Route::resource(
    'contracts',
    \App\Http\Controllers\ContractController::class
  );

  // Contract workflow routes
  Route::post(
    '/contracts/{contract}/submit-for-approval',
    [\App\Http\Controllers\ContractController::class, 'submitForApproval']
  )->name('contracts.submit-for-approval');

  Route::post(
    '/contracts/{contract}/approve',
    [\App\Http\Controllers\ContractController::class, 'approve']
  )->name('contracts.approve');

  Route::post(
    '/contracts/{contract}/reject',
    [\App\Http\Controllers\ContractController::class, 'reject']
  )->name('contracts.reject');

  Route::post(
    '/contracts/{contract}/sign-company',
    [\App\Http\Controllers\ContractController::class, 'signAsCompany']
  )->name('contracts.sign-company');

  Route::get(
    '/contracts/{contract}/export-pdf',
    [\App\Http\Controllers\ContractController::class, 'exportPdf']
  )->name('contracts.export-pdf');
});

Route::get('/contracts/{contract}/client-sign', function (\App\Models\Contract $contract) {
  return Inertia::render('contracts/ClientSign', [
    'contract' => $contract->load('billboards')
  ]);
})->name('contracts.client-sign');

Route::post('/contracts/{contract}/sign-client', [
  \App\Http\Controllers\ContractController::class,
  'signAsClient'
])->name('contracts.sign-client');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/test.php';
