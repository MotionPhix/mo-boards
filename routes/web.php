<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Team invitation accept route - publicly accessible
Route::get(
  '/team/invitation/{token}',
  [App\Http\Controllers\TeamController::class, 'acceptInvitation']
)->name('team.accept-invitation');

Route::middleware(['auth', 'verified', 'ensure.company.access'])->group(function () {
    Route::get(
        '/dashboard',
        [App\Http\Controllers\DashboardController::class, 'index']
    )->name('dashboard');

    // Company routes
    Route::resource(
        'companies',
        App\Http\Controllers\CompanyController::class
    )->only(['index', 'create', 'store']);

    Route::post(
        '/companies/{company}/switch',
        App\Http\Controllers\Company\SwitchCompanyController::class
    )->name('companies.switch');

    // Company settings routes
    Route::get(
        '/companies/settings',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'index']
    )->name('companies.settings');

    Route::get(
        '/companies/settings/profile',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'index']
    )->name('companies.settings.profile');

    Route::get(
        '/companies/settings/numbering',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'index']
    )->name('companies.settings.numbering');

    Route::get(
        '/companies/settings/notifications',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'index']
    )->name('companies.settings.notifications');

    Route::get(
        '/companies/settings/social',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'index']
    )->name('companies.settings.social');

    Route::get(
        '/companies/settings/business',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'index']
    )->name('companies.settings.business');

    Route::post(
        '/companies/settings',
        [App\Http\Controllers\Company\CompanySettingsController::class, 'update']
    )->name('companies.settings.update');

    // Company Billing
    Route::get(
        '/companies/settings/billing',
        [App\Http\Controllers\Company\CompanyBillingController::class, 'index']
    )->name('companies.settings.billing');

    Route::post(
        '/companies/settings/billing/subscribe',
        App\Http\Controllers\Company\SubscribeCompanyPlanController::class
    )->name('companies.settings.billing.subscribe');

    Route::get(
        '/companies/settings/billing/callback',
        App\Http\Controllers\Company\CompanyBillingCallbackController::class
    )->name('companies.settings.billing.callback');

    // Billing: transactions list and receipt download
    // Billing: transactions list and receipt download (mapped to RESTful index/show)
    Route::get(
        '/companies/settings/billing/transactions',
        [App\Http\Controllers\BillingController::class, 'index']
    )->name('companies.settings.billing.transactions');

    Route::get(
        '/companies/settings/billing/transactions/{transaction}/receipt',
        [App\Http\Controllers\BillingController::class, 'show']
    )->name('companies.settings.billing.transactions.receipt');

    // Billboard routes
    Route::resource(
        'billboards',
        App\Http\Controllers\BillboardController::class
    )->parameters(['billboards' => 'billboard:uuid']);

    // Additional billboard routes
    Route::post(
        '/billboards/{billboard:uuid}/duplicate',
        App\Http\Controllers\Billboard\DuplicateBillboardController::class
    )->name('billboards.duplicate');

    // Media deletion route removed to keep controller RESTful according to arch rules.

    Route::post(
        '/billboards/bulk-update',
        App\Http\Controllers\Billboard\BulkUpdateBillboardsController::class
    )->name('billboards.bulk-update');

    Route::get(
        '/billboards/search',
        App\Http\Controllers\Billboard\SearchBillboardsController::class
    )->name('billboards.search');

    Route::get(
        '/billboards/export',
        App\Http\Controllers\Billboard\ExportBillboardsController::class
    )->middleware('plan.feature:export.enabled')
        ->name('billboards.export');

    // Team modal routes for Inertia UI Modal (must be before resource routes)
    Route::get(
        '/team/invite',
        [App\Http\Controllers\TeamController::class, 'inviteModal']
    )->name('team.invite-modal');

    Route::get(
        '/team/{member}/edit',
        [App\Http\Controllers\TeamController::class, 'editModal']
    )->name('team.edit-modal');

    // Team routes
    Route::resource(
        'team',
        App\Http\Controllers\TeamController::class
    )->except(['show']);

    Route::post(
        '/team/invite',
        [App\Http\Controllers\TeamController::class, 'invite']
    )->middleware('plan.limit:team.members.max,company.team.members.count')
        ->name('team.invite');

    Route::delete(
        '/team/invitations/{invitation}',
        [App\Http\Controllers\TeamController::class, 'cancelInvitation']
    )->name('team.cancel-invitation');

    // Contract Template specific routes (must be before resource routes)
    Route::get(
        '/template-marketplace',
        [App\Http\Controllers\ContractTemplateController::class, 'marketPlace']
    )->name('contract-templates.marketplace');

    Route::get(
        '/contract-templates/{contractTemplate:uuid}/preview',
        [App\Http\Controllers\ContractTemplateController::class, 'preview']
    )->name('contract-templates.preview');

    Route::post(
        '/contract-templates/{contractTemplate:uuid}/purchase',
        [App\Http\Controllers\ContractTemplateController::class, 'purchase']
    )->name('contract-templates.purchase');

    Route::post(
        '/contract-templates/{contractTemplate:uuid}/process-payment',
        [App\Http\Controllers\ContractTemplateController::class, 'processPayment']
    )->name('contract-templates.process-payment');

    Route::get(
        '/contract-templates/payment/callback',
        [App\Http\Controllers\ContractTemplateController::class, 'paymentCallback']
    )->name('contract-templates.payment-callback');

    Route::post(
        '/contract-templates/{contractTemplate:uuid}/duplicate',
        App\Http\Controllers\ContractTemplate\DuplicateContractTemplateController::class
    )->name('contract-templates.duplicate');

    Route::get(
        '/contract-templates/{contractTemplate:uuid}/export-pdf',
        [App\Http\Controllers\ContractTemplateController::class, 'exportPdf']
    )->name('contract-templates.export-pdf');

    Route::resource(
        'contract-templates',
        App\Http\Controllers\ContractTemplateController::class
    )->parameters(['contract-templates' => 'contractTemplate:uuid']);

    // Contract routes
    Route::resource(
        'contracts',
        App\Http\Controllers\ContractController::class
    )->parameters(['contracts' => 'contract:uuid']);

    Route::get(
        '/contracts/{contract:uuid}/document',
        App\Http\Controllers\Contract\ContractDocumentRedirectController::class
    )->name('contracts.document');

    Route::get(
        '/contracts/{contract:uuid}/document/show',
        [App\Http\Controllers\ContractController::class, 'documentShow']
    )->name('contracts.document.show');

    Route::get(
        '/contracts/{contract:uuid}/document/edit',
        [App\Http\Controllers\ContractController::class, 'documentEdit']
    )->name('contracts.document.edit');

    Route::put(
        '/contracts/{contract:uuid}/document',
        [App\Http\Controllers\Contract\ContractDocumentController::class, 'update']
    )->name('contracts.document.update');

    Route::get(
        '/contracts/{contract:uuid}/document/pdf',
        [App\Http\Controllers\ContractController::class, 'documentPdf']
    )->name('contracts.document.pdf');

    Route::patch(
        '/contracts/{contract:uuid}/document',
        [App\Http\Controllers\Contract\ContractDocumentController::class, 'update']
    )->name('contracts.update-document');

    Route::post(
        '/contracts/{contract:uuid}/preview-placeholders',
        App\Http\Controllers\Contract\PreviewContractPlaceholdersController::class
    )->name('contracts.preview-placeholders');

    Route::get(
        '/contracts/{contract:uuid}/template-selector',
        App\Http\Controllers\Contract\ContractTemplateSelectorController::class
    )->name('contracts.template-selector');

    Route::patch(
        '/contracts/{contract:uuid}/apply-template',
        App\Http\Controllers\Contract\ApplyContractTemplateController::class
    )->name('contracts.apply-template');

    // Contract workflow routes
    Route::post(
        '/contracts/{contract:uuid}/submit-for-approval',
        [App\Http\Controllers\ContractController::class, 'submitForApproval']
    )->name('contracts.submit-for-approval');

    Route::post(
        '/contracts/{contract:uuid}/approve',
        [App\Http\Controllers\ContractController::class, 'approve']
    )->name('contracts.approve');

    Route::post(
        '/contracts/{contract:uuid}/reject',
        [App\Http\Controllers\ContractController::class, 'reject']
    )->name('contracts.reject');

    Route::post(
        '/contracts/{contract:uuid}/sign-company',
        [App\Http\Controllers\ContractController::class, 'signAsCompany']
    )->name('contracts.sign-company');

    Route::get(
        '/contracts/{contract:uuid}/export-pdf',
        [App\Http\Controllers\ContractController::class, 'exportPdf']
    )->name('contracts.export-pdf');

    // System Notifications API routes
    Route::prefix('api/notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\SystemNotificationController::class, 'index'])
            ->name('notifications.index');
        
        Route::post('/{notification}/read', [App\Http\Controllers\SystemNotificationController::class, 'markAsRead'])
            ->name('notifications.mark-read');
        
        Route::post('/{notification}/dismiss', [App\Http\Controllers\SystemNotificationController::class, 'dismiss'])
            ->name('notifications.dismiss');
        
        Route::post('/mark-all-read', [App\Http\Controllers\SystemNotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-read');
        
        Route::get('/unread-count', [App\Http\Controllers\SystemNotificationController::class, 'unreadCount'])
            ->name('notifications.unread-count');
    });
});

Route::get('/contracts/{contract:uuid}/client-sign', function (App\Models\Contract $contract) {
    return Inertia::render('contracts/ClientSign', [
        'contract' => $contract->load('billboards'),
    ]);
})->name('contracts.client-sign');

Route::post('/contracts/{contract:uuid}/sign-client', [
    App\Http\Controllers\ContractController::class,
    'signAsClient',
])->name('contracts.sign-client');

// Public webhook endpoint (do not place under auth middleware)
Route::post(
    '/webhooks/paychangu',
    App\Http\Controllers\PayChanguWebhookController::class
)->name('webhooks.paychangu');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/test.php';
