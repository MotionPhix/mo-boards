<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Team invitation accept route - publicly accessible
Route::get('/team/invitation/{token}', [\App\Http\Controllers\TeamController::class, 'acceptInvitation'])
    ->name('team.accept-invitation');

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

  // Company settings routes
  Route::get(
    '/companies/settings',
    [\App\Http\Controllers\CompanyController::class, 'settings']
  )->name('companies.settings');

  Route::post(
    '/companies/settings',
    [\App\Http\Controllers\CompanyController::class, 'updateSettings']
  )->name('companies.settings.update');

  // Billboard routes
  Route::resource(
    'billboards',
    \App\Http\Controllers\BillboardController::class
  )->parameters(['billboards' => 'billboard:uuid']);

  // Additional billboard routes
  Route::post(
    '/billboards/{billboard:uuid}/duplicate',
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

  Route::delete(
    '/team/invitations/{invitation}',
    [\App\Http\Controllers\TeamController::class, 'cancelInvitation']
  )->name('team.cancel-invitation');

  // Contract Template specific routes (must be before resource routes)
  Route::get(
    '/template-marketplace',
    [\App\Http\Controllers\ContractTemplateController::class, 'marketPlace']
  )->name('contract-templates.marketplace');

  Route::get(
    '/contract-templates/{contractTemplate:uuid}/preview',
    [\App\Http\Controllers\ContractTemplateController::class, 'preview']
  )->name('contract-templates.preview');

  Route::post(
    '/contract-templates/{contractTemplate:uuid}/purchase',
    [\App\Http\Controllers\ContractTemplateController::class, 'purchase']
  )->name('contract-templates.purchase');

  Route::post(
    '/contract-templates/{contractTemplate:uuid}/process-payment',
    [\App\Http\Controllers\ContractTemplateController::class, 'processPayment']
  )->name('contract-templates.process-payment');

  Route::get(
    '/contract-templates/payment/callback',
    [\App\Http\Controllers\ContractTemplateController::class, 'paymentCallback']
  )->name('contract-templates.payment-callback');

  Route::post(
    '/contract-templates/{contractTemplate:uuid}/duplicate',
    [\App\Http\Controllers\ContractTemplateController::class, 'duplicate']
  )->name('contract-templates.duplicate');

  Route::get(
    '/contract-templates/{contractTemplate:uuid}/export-pdf',
    [\App\Http\Controllers\ContractTemplateController::class, 'exportPdf']
  )->name('contract-templates.export-pdf');

  Route::resource(
    'contract-templates',
    \App\Http\Controllers\ContractTemplateController::class
  )->parameters(['contract-templates' => 'contractTemplate:uuid']);

  // Contract routes
  Route::resource(
    'contracts',
    \App\Http\Controllers\ContractController::class
  )->parameters(['contracts' => 'contract:uuid']);

  Route::get(
    '/contracts/{contract:uuid}/document',
    [\App\Http\Controllers\ContractController::class, 'document']
  )->name('contracts.document');

  Route::get(
    '/contracts/{contract:uuid}/document/show',
    [\App\Http\Controllers\ContractController::class, 'documentShow']
  )->name('contracts.document.show');

  Route::get(
    '/contracts/{contract:uuid}/document/edit',
    [\App\Http\Controllers\ContractController::class, 'documentEdit']
  )->name('contracts.document.edit');

  Route::put(
    '/contracts/{contract:uuid}/document',
    [\App\Http\Controllers\ContractController::class, 'documentUpdate']
  )->name('contracts.document.update');

  Route::get(
    '/contracts/{contract:uuid}/document/pdf',
    [\App\Http\Controllers\ContractController::class, 'documentPdf']
  )->name('contracts.document.pdf');

  Route::patch(
    '/contracts/{contract:uuid}/document',
    [\App\Http\Controllers\ContractController::class, 'updateDocument']
  )->name('contracts.update-document');

  Route::post(
    '/contracts/{contract:uuid}/preview-placeholders',
    [\App\Http\Controllers\ContractController::class, 'previewPlaceholders']
  )->name('contracts.preview-placeholders');

  Route::get(
    '/contracts/{contract:uuid}/template-selector',
    [\App\Http\Controllers\ContractController::class, 'templateSelector']
  )->name('contracts.template-selector');

  Route::patch(
    '/contracts/{contract:uuid}/apply-template',
    [\App\Http\Controllers\ContractController::class, 'applyTemplate']
  )->name('contracts.apply-template');

  // Contract workflow routes
  Route::post(
    '/contracts/{contract:uuid}/submit-for-approval',
    [\App\Http\Controllers\ContractController::class, 'submitForApproval']
  )->name('contracts.submit-for-approval');

  Route::post(
    '/contracts/{contract:uuid}/approve',
    [\App\Http\Controllers\ContractController::class, 'approve']
  )->name('contracts.approve');

  Route::post(
    '/contracts/{contract:uuid}/reject',
    [\App\Http\Controllers\ContractController::class, 'reject']
  )->name('contracts.reject');

  Route::post(
    '/contracts/{contract:uuid}/sign-company',
    [\App\Http\Controllers\ContractController::class, 'signAsCompany']
  )->name('contracts.sign-company');

  Route::get(
    '/contracts/{contract:uuid}/export-pdf',
    [\App\Http\Controllers\ContractController::class, 'exportPdf']
  )->name('contracts.export-pdf');
});

Route::get('/contracts/{contract:uuid}/client-sign', function (\App\Models\Contract $contract) {
  return Inertia::render('contracts/ClientSign', [
    'contract' => $contract->load('billboards')
  ]);
})->name('contracts.client-sign');

Route::post('/contracts/{contract:uuid}/sign-client', [
  \App\Http\Controllers\ContractController::class,
  'signAsClient'
])->name('contracts.sign-client');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/test.php';
