<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use App\Models\PurchasedTemplate;
use App\Models\TemplatePaymentTransaction;
use App\Services\PlaceholderService;
use App\Services\ContractTemplateService;
use App\Services\PayChangu\PayChanguService;
use App\Services\PayChangu\PayChanguPaymentRequest;
use HosmelQ\NameOfPerson\PersonName;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class ContractTemplateController extends Controller
{
  use AuthorizesRequests;

  protected ContractTemplateService $templateService;

  public function __construct(ContractTemplateService $templateService)
  {
    $this->templateService = $templateService;
  }

  public function index(Request $request): Response
  {
    $data = $this->templateService->getTemplatesForIndex($request);

    // Add available categories for the filter dropdown
    $data['categories'] = $this->templateService->getAvailableCategories();

    return Inertia::render('contract-templates/Index', $data);
  }

  public function create(Request $request): Response
  {
    // Check if this is a modal request
    $isModal = $request->query('modal', false);

    if ($isModal) {
      return Inertia::render('contract-templates/ModalCreate');
    }

    // Get available categories for the dropdown
    $categories = $this->templateService->getAvailableCategories();

    // Provide available placeholders to help authors insert variables
    $placeholders = PlaceholderService::getAvailablePlaceholders();

    return Inertia::render('contract-templates/Create', [
      'categories' => $categories,
      'placeholders' => $placeholders,
    ]);
  }

  public function store(Request $request)
  {
    $company = $request->user()->currentCompany;

    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:255',
        // Unique template name per company
        function ($attribute, $value, $fail) use ($company) {
          $exists = ContractTemplate::where('company_id', $company->id)
            ->where('name', $value)
            ->exists();

          if ($exists) {
            $fail('A template with this name already exists for your company.');
          }
        }
      ],
      'description' => 'nullable|string',
      'content' => 'nullable|string',
      'category' => 'nullable|string|max:255',
      'default_terms' => 'nullable|array',
      'custom_fields' => 'nullable|array',
      'is_active' => 'boolean',
      'is_premium' => 'boolean',
      'price' => 'nullable|numeric|min:0',
    ]);

    // Check if user is super admin for premium template creation
    $user = $request->user();
    $isSuperAdmin = $user->hasRole('super_admin');

    // Provide default values for required fields if not present
    $templateData = [
      'name' => $validated['name'],
      'description' => $validated['description'] ?? null,
      'content' => $validated['content'] ?? null,
      'category' => $validated['category'] ?? null,
      'default_terms' => $validated['default_terms'] ?? [], // Default to empty array
      'custom_fields' => $validated['custom_fields'] ?? [], // Default to empty array
      'is_active' => $validated['is_active'] ?? true,
      'is_system_template' => $isSuperAdmin && ($validated['is_premium'] ?? false),
      'is_premium' => $isSuperAdmin && ($validated['is_premium'] ?? false),
      'price' => ($isSuperAdmin && ($validated['is_premium'] ?? false)) ? ($validated['price'] ?? 0) : null,
      'company_id' => ($isSuperAdmin && ($validated['is_premium'] ?? false)) ? null : $company->id,
    ];

    ContractTemplate::create($templateData);

    return redirect()
    ->back()
    ->with('success', 'Template created successfully.');
  }

  public function show(ContractTemplate $contractTemplate): Response
  {
    $contractTemplate->load(['contracts' => function ($query) {
      $query->latest()->limit(10);
    }]);

    return Inertia::render('contract-templates/Show', [
      'template' => $contractTemplate
    ]);
  }

  public function edit(ContractTemplate $contractTemplate): Response
  {
    // Get available categories for the dropdown
    $categories = $this->templateService->getAvailableCategories();

    return Inertia::render('contract-templates/Edit', [
      'template' => $contractTemplate,
      'categories' => $categories
    ]);
  }

  public function update(Request $request, ContractTemplate $contractTemplate)
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:255',
        // Unique template name per company (excluding current template)
        function ($attribute, $value, $fail) use ($contractTemplate) {
          $exists = ContractTemplate::where('company_id', $contractTemplate->company_id)
            ->where('name', $value)
            ->where('id', '!=', $contractTemplate->id)
            ->exists();

          if ($exists) {
            $fail('A template with this name already exists for your company.');
          }
        }
      ],
      'description' => 'nullable|string',
      'content' => 'nullable|string',
      'category' => 'nullable|string|max:255',
      'default_terms' => 'nullable|array',
      'custom_fields' => 'nullable|array',
      'is_active' => 'boolean',
      'is_premium' => 'boolean',
      'price' => 'nullable|numeric|min:0',
    ]);

    $contractTemplate->update($validated);

    return redirect()
      ->back()
      ->with('success', 'Template is new now!');
  }

  public function destroy(ContractTemplate $contractTemplate)
  {
    $contractTemplate->delete();

    return redirect()->route('contract-templates.index')
      ->with('success', 'Template deleted successfully.');
  }

  public function duplicate(ContractTemplate $contractTemplate)
  {
    $company = Auth::user()->currentCompany;

    $duplicatedTemplate = ContractTemplate::create([
      'name' => $contractTemplate->name . ' (Copy)',
      'description' => $contractTemplate->description,
      'content' => $contractTemplate->content,
      'default_terms' => $contractTemplate->default_terms ?? [],
      'custom_fields' => $contractTemplate->custom_fields ?? [],
      'is_active' => true,
      'company_id' => $company->id,
    ]);

    return response()->json([
      'message' => 'Template duplicated successfully',
      'template' => $duplicatedTemplate
    ]);
  }

  public function preview(ContractTemplate $contractTemplate): Response
  {
    // Only allow preview of system templates
    if (!$contractTemplate->is_system_template) {
      abort(404);
    }

    $company = Auth::user()->currentCompany;
    $isPurchased = $contractTemplate->isPurchasedBy($company->id);

    return Inertia::render('contract-templates/Preview', [
      'template' => $contractTemplate,
      'previewContent' => $contractTemplate->getPreviewContent($company->id),
      'isPurchased' => $isPurchased,
      'canPurchase' => !$isPurchased,
    ]);
  }

  public function purchase(Request $request, ContractTemplate $contractTemplate)
  {
    // Only allow purchase of system templates
    if (!$contractTemplate->is_system_template) {
      abort(404, 'This template is not available for purchase.');
    }

    $company = Auth::user()->currentCompany;

    // Check if already purchased
    if ($contractTemplate->isPurchasedBy($company->id)) {
      return redirect()->back()->with('error', 'Template already purchased.');
    }

    // Generate unique reference
    $reference = TemplatePaymentTransaction::generateReference();
    $userInfo = new PersonName( Auth::user()->name);

    // Create payment transaction record
    $transaction = TemplatePaymentTransaction::create([
      'company_id' => $company->id,
      'template_id' => $contractTemplate->id,
      'user_id' => Auth::id(),
      'payment_id' => '', // Will be updated after PayChangu response
      'reference' => $reference,
      'amount' => $contractTemplate->price,
      'currency' => config('paychangu.currency', 'MWK'),
      'status' => 'pending',
      'return_url' => route('contract-templates.marketplace'),
      'cancel_url' => route('contract-templates.marketplace'),
      'customer_details' => [
        'first_name' => $userInfo->first,
        'last_name' => $userInfo->last,
        'email' => Auth::user()->email,
        'phone' => Auth::user()->phone ?? null,
      ],
      'metadata' => [
        'template_id' => $contractTemplate->id,
        'template_name' => $contractTemplate->name,
        'company_id' => $company->id,
        'company_name' => $company->name,
      ],
      'payment_initiated_at' => now(),
    ]);

    return Inertia::render('contract-templates/PaymentCheckout', [
      'template' => $contractTemplate,
      'transaction' => $transaction,
      'company' => $company,
    ]);
  }

  public function processPayment(Request $request, ContractTemplate $contractTemplate)
  {
    $request->validate([
      'transaction_id' => 'required|exists:template_payment_transactions,id',
      'payment_method' => 'required|string|in:mobile_money,bank_card',
    ]);

    $transaction = TemplatePaymentTransaction::findOrFail($request->transaction_id);

    // Verify transaction belongs to current user's company
    if ($transaction->company_id !== Auth::user()->currentCompany->id) {
      abort(403, 'Unauthorized access to transaction.');
    }

    try {
      $payChanguService = new PayChanguService();

      // Create PayChangu payment request
      $paymentRequest = PayChanguPaymentRequest::create([
        'amount' => $transaction->amount,
        'currency' => $transaction->currency,
        'reference' => $transaction->reference,
        'return_url' => $transaction->return_url,
        'cancel_url' => $transaction->cancel_url,
        'customer_first_name' => $transaction->customer_details['first_name'],
        'customer_last_name' => $transaction->customer_details['last_name'],
        'customer_email' => $transaction->customer_details['email'],
        'customer_phone' => $transaction->customer_details['phone'],
        'metadata' => array_merge($transaction->metadata, [
          'payment_method' => $request->payment_method,
          'transaction_id' => $transaction->id,
        ]),
      ]);

      // Create payment with PayChangu
      $paymentResponse = $payChanguService->createPayment($paymentRequest);

      // Update transaction with PayChangu payment details
      $transaction->update([
        'payment_id' => $paymentResponse->payment_id,
        'checkout_url' => $paymentResponse->checkout_url,
        'status' => 'processing',
        'paychangu_response' => $paymentResponse->toArray(),
      ]);

      // Redirect to PayChangu checkout
      return Inertia::location($paymentResponse->checkout_url);

    } catch (\Exception $e) {
      Log::error('Payment processing failed', [
        'transaction_id' => $transaction->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $transaction->markAsFailed($e->getMessage());

      return redirect()->back()->with('error', 'Payment processing failed. Please try again.');
    }
  }

  public function paymentCallback(Request $request)
  {
    try {
      // Validate PayChangu webhook signature if configured
      $payChanguService = new PayChanguService();
      $payload = $request->getContent();
      $signature = $request->header('X-PayChangu-Signature');

      if ($signature && !$payChanguService->validateWebhookSignature($payload, $signature)) {
        Log::warning('Invalid PayChangu webhook signature', [
          'signature' => $signature,
          'payload' => $payload
        ]);
        return response('Invalid signature', 400);
      }

      $paymentData = $request->all();
      $paymentId = $paymentData['payment_id'] ?? null;

      if (!$paymentId) {
        Log::error('PayChangu callback missing payment_id', $paymentData);
        return response('Missing payment_id', 400);
      }

      // Find transaction by payment_id
      $transaction = TemplatePaymentTransaction::where('payment_id', $paymentId)->first();

      if (!$transaction) {
        Log::error('Transaction not found for payment_id', ['payment_id' => $paymentId]);
        return response('Transaction not found', 404);
      }

      // Verify payment with PayChangu
      $verification = $payChanguService->verifyPayment($paymentId);

      DB::transaction(function () use ($transaction, $verification, $paymentData) {
        if ($verification->isPaid()) {
          // Mark transaction as completed
          $transaction->markAsCompleted($verification->toArray());

          // Create purchase record if not exists
          if (!PurchasedTemplate::where('company_id', $transaction->company_id)
                ->where('template_id', $transaction->template_id)
                ->exists()) {

            PurchasedTemplate::create([
              'company_id' => $transaction->company_id,
              'template_id' => $transaction->template_id,
              'purchase_price' => $transaction->amount,
              'purchased_at' => now(),
              'purchased_by' => $transaction->user_id,
              'purchase_metadata' => [
                'payment_method' => 'paychangu',
                'transaction_id' => $transaction->id,
                'payment_id' => $transaction->payment_id,
                'paychangu_verification' => $verification->toArray(),
              ],
            ]);
          }

          Log::info('Template purchase completed', [
            'transaction_id' => $transaction->id,
            'template_id' => $transaction->template_id,
            'company_id' => $transaction->company_id,
            'amount' => $transaction->amount,
          ]);

        } elseif ($verification->isFailed()) {
          $transaction->markAsFailed($verification->status, $verification->toArray());

          Log::info('Template purchase failed', [
            'transaction_id' => $transaction->id,
            'payment_status' => $verification->status,
          ]);
        }
      });

      return response('OK', 200);

    } catch (\Exception $e) {
      Log::error('PayChangu callback processing failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request_data' => $request->all()
      ]);

      return response('Internal error', 500);
    }
  }

  public function marketPlace(): Response
  {
    $company = Auth::user()->currentCompany;

    // Get all system templates with purchase status
    $systemTemplates = ContractTemplate::systemTemplates()
      ->active()
      ->get()
      ->map(function ($template) use ($company) {
        $template->is_purchased = $template->isPurchasedBy($company->id);
        return $template;
      })
      ->groupBy('category');

    return Inertia::render('contract-templates/MarketPlace', [
      'templatesByCategory' => $systemTemplates,
      'categories' => $systemTemplates->keys(),
      'companyCurrency' => $company->currency ?? 'MWK',
    ]);
  }

  public function exportPdf(ContractTemplate $template)
  {
    $company = Auth::user()->currentCompany;

    // Ensure user has access to this template
    if ($template->isSystem() && !$template->isPurchasedBy($company->id)) {
      abort(403, 'Template not purchased');
    }

    if (!$template->isSystem() && $template->company_id !== $company->id) {
      abort(403, 'Unauthorized access to template');
    }

    try {
      // Generate PDF using DomPDF
      $pdf = Pdf::loadView('pdfs.contract-template', [
        'template' => $template,
        'company' => $company,
      ]);

      // Configure PDF options
      $pdf->setPaper('A4', 'portrait');
      $pdf->setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'dpi' => 150,
        'defaultMediaType' => 'print',
        'isFontSubsettingEnabled' => true,
      ]);

      // Generate a clean filename
      $filename = 'template-' . \Illuminate\Support\Str::slug($template->name) . '-' . date('Y-m-d') . '.pdf';

      // Return the PDF as a download
      return $pdf->download($filename);

    } catch (\Exception $e) {
      Log::error('PDF generation failed', [
        'template_id' => $template->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      // Fallback to JSON response for frontend handling
      return response()->json([
        'error' => 'PDF generation failed',
        'message' => 'Unable to generate PDF. Please try again later.',
        'template' => $template,
        'content' => $template->content,
      ], 500);
    }
  }
}
