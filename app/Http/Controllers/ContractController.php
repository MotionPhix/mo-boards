<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractResource;
use App\Models\Billboard;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\PurchasedTemplate;
use App\Services\ContractTemplateService;
use App\Services\PlaceholderService;
use App\Services\ContractContentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use App\Enums\ContractStatus;

class ContractController extends Controller
{
  use AuthorizesRequests;

  protected $contentService;

  public function __construct(ContractContentService $contentService)
  {
    $this->contentService = $contentService;
  }

  public function index(Request $request): Response
  {
    $company = $request->user()->currentCompany;

    $contracts = Contract::with(['billboards', 'template', 'createdBy', 'company'])
      ->where('company_id', $company->id)
      ->when($request->search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
          $q->where('contract_number', 'like', "%{$search}%")
            ->orWhere('client_name', 'like', "%{$search}%")
            ->orWhere('client_company', 'like', "%{$search}%");
        });
      })
      ->when($request->status, function ($query, $status) {
        $query->where('status', $status);
      })
      ->orderBy('created_at', 'desc')
      ->paginate(15)
      ->withQueryString();

    return Inertia::render('contracts/Index', [
      'contracts' => ContractResource::collection($contracts),
      'filters' => $request->only(['search', 'status']),
      'stats' => [
        'total' => Contract::where('company_id', $company->id)->count(),
        'active' => Contract::where('company_id', $company->id)->where('status', 'active')->count(),
        'draft' => Contract::where('company_id', $company->id)->where('status', 'draft')->count(),
        'expiring_soon' => Contract::where('company_id', $company->id)->expiringSoon()->count(),
      ]
    ]);
  }

  public function create(): Response
  {
    $company = Auth::user()?->currentCompany;

    return Inertia::render('contracts/Create', [
      'templates' => ContractTemplate::where('company_id', $company->id)
        ->active()
        ->get(),
      'billboards' => Billboard::where('company_id', $company->id)
        ->get(['id', 'name', 'code', 'location', 'monthly_rate']),
    ]);
  }

  public function store(Request $request)
  {
    $company = Auth::user()?->currentCompany;

    $validated = $request->validate([
      'template_id' => 'nullable|exists:contract_templates,id',
      'client_name' => 'required|string|max:255',
      'client_email' => 'nullable|email',
      'client_phone' => 'nullable|string|max:20',
      'client_address' => 'nullable|string',
      'client_company' => 'nullable|string|max:255',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
      'payment_terms' => 'required|in:monthly,quarterly,semi_annual,annual,one_time',
      'billboards' => 'required|array|min:1',
      'billboards.*.id' => 'required|exists:billboards,id',
      'billboards.*.rate' => 'required|numeric|min:0',
      'billboards.*.notes' => 'nullable|string',
      'terms_and_conditions' => 'required|array',
      'custom_fields_data' => 'nullable|array',
      'notes' => 'nullable|string',
    ]);

    // Calculate total amount
    $totalRate = collect($validated['billboards'])->sum('rate');
    $months = now()->parse($validated['start_date'])->diffInMonths($validated['end_date']) + 1;
    $totalAmount = $totalRate * $months;

    // Exclude pivot data from model attributes to avoid unknown column errors
    $contractAttributes = Arr::except($validated, ['billboards']);

    $contract = Contract::create([
      ...$contractAttributes,
      'company_id' => $company->id,
      'created_by' => $request->user()->id,
      'total_amount' => $totalAmount,
      'monthly_amount' => $totalRate,
    ]);

    // If created from template, apply template content to contract
    if (isset($validated['template_id'])) {
      $template = ContractTemplate::find($validated['template_id']);
      if ($template) {
        $this->contentService->applyTemplateToContract($contract, $template);
      }
    }

    // Attach billboards with rates
    foreach ($validated['billboards'] as $billboard) {
      $contract->billboards()->attach($billboard['id'], [
        'rate' => $billboard['rate'],
        'notes' => $billboard['notes'] ?? null,
      ]);
    }

    // Generate payment schedule if not one-time
    if ($validated['payment_terms'] !== 'one_time') {
      $this->generatePaymentSchedule($contract);
    }

    return redirect()->route('contracts.show', $contract)
      ->with('success', 'Contract created successfully.');
  }

  public function show(Contract $contract): Response
  {
    $this->authorize('view', $contract);

  // Ensure relationships required for placeholder rendering are available
  $contract->loadMissing(['billboards', 'company', 'template', 'createdBy', 'payments']);

  // Prepare processed HTML content for inline preview using PlaceholderService
  $placeholderService = new PlaceholderService();
  $processedContent = $placeholderService->replacePlaceholders($contract->design ?? '', $contract);

    return Inertia::render('contracts/Show', [
      'contract' => new ContractResource($contract),
      'processedContent' => $processedContent,
    ]);
  }

  public function edit(Request $request, Contract $contract, ContractTemplateService $templateService): Response
  {
    $this->authorize('update', $contract);

    $company = Auth::user()?->currentCompany;

    $contract->load(['billboards', 'company']);

  $templates = $templateService->getTemplatesForEdit($request);

    return Inertia::render('contracts/Edit', [
      'contract' => new ContractResource($contract),
  'templatesOwned' => $templates['owned'] ?? [],
  'templatesPurchased' => $templates['purchased'] ?? [],
      'billboards' => Billboard::where('company_id', $company->id)
        ->get(['id', 'name', 'code', 'location', 'monthly_rate']),
      'currencyOptions' => \App\Helpers\CurrencyHelper::getDropdownOptions(),
      // Provide full placeholder catalog for dynamic dropdowns
      'placeholdersCatalog' => PlaceholderService::getAvailablePlaceholders(),
    ]);
  }

  public function update(Request $request, Contract $contract)
  {
    $this->authorize('update', $contract);

    // Similar validation as store method
    $validated = $request->validate([
      'client_name' => 'required|string|max:255',
      'client_email' => 'nullable|email',
      'client_phone' => 'nullable|string|max:20',
      'client_address' => 'nullable|string',
      'client_company' => 'nullable|string|max:255',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after:start_date',
      'payment_terms' => 'required|in:monthly,quarterly,semi_annual,annual,one_time',
      // Allow zero billboards in edit, but enforce status rules below
      'billboards' => 'required|array|min:0',
      'billboards.*.id' => 'required|exists:billboards,id',
      'billboards.*.rate' => 'required|numeric|min:0',
      'billboards.*.notes' => 'nullable|string',
  'terms_and_conditions' => 'nullable|array',
      'custom_fields_data' => 'nullable|array',
      'notes' => 'nullable|string',
  'status' => 'sometimes|' . ContractStatus::validationRule(),
      'currency' => 'sometimes|string|size:3',
      'design' => 'sometimes|nullable|string',
      'content' => 'sometimes|nullable|string',
    ]);

    // If no billboards, force status to draft
    if ((isset($validated['billboards']) && count($validated['billboards']) === 0)) {
      $validated['status'] = 'draft';
    }

    // Recalculate amounts
    $totalRate = collect($validated['billboards'])->sum('rate');
    $months = now()->parse($validated['start_date'])->diffInMonths($validated['end_date']) + 1;
    $totalAmount = $totalRate * $months;

    $updatePayload = [
      ...Arr::except($validated, ['billboards']),
      'total_amount' => $totalAmount,
      'monthly_amount' => $totalRate,
    ];

    // If content provided, process placeholders and store as document_content
    if ($request->has('content')) {
      $placeholderService = new \App\Services\PlaceholderService();
      $processedContent = $placeholderService->replacePlaceholders($request->input('content') ?? '', $contract);
      $updatePayload['document_content'] = $processedContent;
    }

    $contract->update($updatePayload);

    // Update billboard relationships
    $contract->billboards()->detach();
    foreach ($validated['billboards'] as $billboard) {
      $contract->billboards()->attach($billboard['id'], [
        'rate' => $billboard['rate'],
        'notes' => $billboard['notes'] ?? null,
      ]);
    }

    return redirect()->route('contracts.show', $contract)
      ->with('success', 'Contract updated successfully.');
  }

  public function destroy(Contract $contract)
  {
    $this->authorize('delete', $contract);

    $contract->delete();

    return redirect()->route('contracts.index')
      ->with('success', 'Contract deleted successfully.');
  }

  public function document(Contract $contract): RedirectResponse
  {
  // Consolidated into contracts.show; redirect for backward compatibility
  return redirect()->route('contracts.show', $contract->uuid);
  }

  public function updateDocument(Request $request, Contract $contract)
  {
    $this->authorize('update', $contract);

    $request->validate([
      'content' => 'required|string',
    ]);

    // Process placeholders in the content
    $placeholderService = new PlaceholderService();
    $processedContent = $placeholderService->replacePlaceholders($request->content, $contract);

    $contract->update([
      'document_content' => $processedContent,
    ]);

    return redirect()->back()->with('success', 'Document content updated successfully');
  }

  public function previewPlaceholders(Request $request, Contract $contract)
  {
    $this->authorize('view', $contract);

    $request->validate([
      'content' => 'required|string',
    ]);

    // Process placeholders for preview
    $placeholderService = new PlaceholderService();
  // Ensure relationships needed for special placeholders are loaded
  $contract->loadMissing(['billboards', 'company']);
    $previewContent = $placeholderService->replacePlaceholders($request->content, $contract);
  $placeholderValues = $placeholderService->getPlaceholderValues($contract);
  // Ensure special layout placeholders are visible in the summary
  $placeholderValues['{{billboards_table}}'] = 'Rendered billboards table layout';
  $placeholderValues['{{billboards_list}}'] = 'Rendered billboards list layout';

    return response()->json([
      'preview_content' => $previewContent,
      'placeholder_values' => $placeholderValues,
    ]);
  }

  public function templateSelector(Request $request, Contract $contract): Response
  {
    $this->authorize('view', $contract);

    $company = $request->user()->currentCompany;

    return Inertia::render('contracts/partials/TemplateSelectionModal', [
      'templates' => ContractTemplate::where('company_id', $company->id)
        ->active()
        ->get(['id', 'name', 'description', 'content', 'is_active', 'created_at', 'updated_at']),
      'selectedTemplateId' => $contract->template_id,
      'contractId' => $contract->id,
    ]);
  }

  public function applyTemplate(Request $request, Contract $contract)
  {
    $this->authorize('update', $contract);

    $request->validate([
      'template_id' => 'required|exists:contract_templates,id',
      'content' => 'required|string',
    ]);

    // Process placeholders in the template content
    $placeholderService = new PlaceholderService();
    $processedContent = $placeholderService->replacePlaceholders($request->content, $contract);

    $contract->update([
      'template_id' => $request->template_id,
      'document_content' => $processedContent,
    ]);

    // Return an Inertia response that will allow the modal to close properly
    return redirect()->back()->with('success', 'Template applied successfully');
  }

  public function exportPdf(Contract $contract)
  {
    $this->authorize('view', $contract);

    try {
  // Ensure required relations are present for placeholder rendering
  $contract->loadMissing(['billboards', 'company']);

  // Render placeholders using the dedicated service (includes billboards_table/list)
  $placeholderService = new PlaceholderService();
  $processedContent = $placeholderService->replacePlaceholders($contract->design ?? '', $contract);

  // Minimal PDF: render only the processed content from the contract design
  $pdf = Pdf::loadView('pdfs.contract-raw', compact('processedContent'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
          'defaultFont' => 'DejaVu Sans',
          'isRemoteEnabled' => true,
          'isHtml5ParserEnabled' => true,
          'dpi' => 150,
          'defaultPaperSize' => 'a4',
          'margin' => [
            'top' => 20,
            'right' => 20,
            'bottom' => 20,
            'left' => 20
          ]
        ]);

      $filename = 'contract-' . $contract->contract_number . '.pdf';

      return $pdf->download($filename);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'Failed to generate PDF: ' . $e->getMessage()
      ], 500);
    }
  }

  private function generatePaymentSchedule(Contract $contract): void
  {
    $interval = match ($contract->payment_terms) {
      'monthly' => 1,
      'quarterly' => 3,
      'semi_annual' => 6,
      'annual' => 12,
      default => 1,
    };

    $paymentAmount = $contract->monthly_amount * $interval;
    $currentDate = $contract->start_date->copy();

    while ($currentDate <= $contract->end_date) {
      $contract->payments()->create([
        'amount' => $paymentAmount,
        'due_date' => $currentDate->copy(),
        'status' => 'pending',
      ]);

      $currentDate->addMonths($interval);
    }
  }

  private function getDefaultContent(Contract $contract): string
  {
    return '
      <h1>BILLBOARD ADVERTISING AGREEMENT</h1>

      <p>This Billboard Advertising Agreement ("Agreement") is entered into on <strong>' . now()->format('F j, Y') . '</strong> between:</p>

      <h2>PARTIES</h2>
      <p><strong>Advertiser (Client):</strong><br>
      ' . $contract->client_name . '<br>
      ' . ($contract->client_company ?? '') . '<br>
      ' . ($contract->client_address ?? '') . '<br>
      Email: ' . ($contract->client_email ?? '') . '<br>
      Phone: ' . ($contract->client_phone ?? '') . '</p>

      <p><strong>Billboard Company:</strong><br>
      ' . $contract->company->name . '<br>
      ' . ($contract->company->address ?? '') . '</p>

      <h2>BILLBOARD ADVERTISING SERVICES</h2>
      <p>The Company agrees to provide billboard advertising services at the following location(s):</p>

      <p><strong>Billboard Locations:</strong></p>
      <ul>
      ' . $contract->billboards->map(fn($billboard) => '<li>' . $billboard->name . ' - ' . $billboard->location . '</li>')->join('') . '
      </ul>

      <h2>TERMS AND CONDITIONS</h2>

      <h3>1. Contract Period</h3>
      <p>This agreement shall commence on <strong>' . $contract->start_date->format('F j, Y') . '</strong> and continue until <strong>' . $contract->end_date->format('F j, Y') . '</strong>, unless terminated earlier in accordance with the terms herein.</p>

      <h3>2. Advertising Fee</h3>
      <p><strong>Monthly Fee:</strong> $' . number_format($contract->monthly_amount, 2) . '<br>
      <strong>Total Contract Value:</strong> $' . number_format($contract->total_amount, 2) . '<br>
      <strong>Payment Terms:</strong> ' . ucfirst(str_replace('_', ' ', $contract->payment_terms)) . '</p>

      <h3>3. Terms and Conditions</h3>
      <p>This agreement is subject to the standard terms and conditions as outlined in our contract template.</p>

      <div style="margin-top: 4rem;">
        <div style="display: flex; justify-content: space-between;">
          <div style="width: 45%;">
            <p>_________________________<br>Client Signature</p>
            <p>Date: ___________________</p>
          </div>
          <div style="width: 45%;">
            <p>_________________________<br>Company Representative</p>
            <p>Date: ___________________</p>
          </div>
        </div>
      </div>
    ';
  }

  /**
   * Show the document with processed placeholders (read-only view)
   */
  public function documentShow(Contract $contract): RedirectResponse
  {
    // Consolidated into contracts.show; redirect for backward compatibility
    return redirect()->route('contracts.show', $contract->uuid);
  }

  /**
   * Show the editable document design form
   */
  public function documentEdit(Contract $contract): RedirectResponse
  {
  // Consolidated into contracts.edit; redirect for backward compatibility
  return redirect()->route('contracts.edit', $contract->uuid);
  }

  /**
   * Update the contract design
   */
  public function documentUpdate(Request $request, Contract $contract)
  {
    $this->authorize('update', $contract);

    $validated = $request->validate([
      'design' => 'required|string',
      'custom_field_values' => 'nullable|array',
    ]);

    $contract->update([
      'design' => $validated['design'],
      'custom_field_values' => $validated['custom_field_values'] ?? [],
    ]);

    // Process the content after updating design
    $contentService = new ContractContentService();
    $contentService->processContractContent($contract);

    return redirect()->route('contracts.document.show', $contract->uuid)
      ->with('success', 'Contract document updated successfully.');
  }

  /**
   * Export document as PDF
   */
  public function documentPdf(Contract $contract)
  {
    $this->authorize('view', $contract);

    try {
  // Load required relations
  $contract->loadMissing(['billboards', 'company']);

  // Render placeholders using PlaceholderService
  $placeholderService = new PlaceholderService();
  $processedContent = $placeholderService->replacePlaceholders($contract->design ?? '', $contract);

  $pdf = Pdf::loadView('pdfs.contract-raw', compact('processedContent'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
          'defaultFont' => 'DejaVu Sans',
          'isRemoteEnabled' => true,
          'isHtml5ParserEnabled' => true,
          'dpi' => 150,
          'defaultPaperSize' => 'a4',
          'margin' => [
            'top' => 20,
            'right' => 20,
            'bottom' => 20,
            'left' => 20
          ]
        ]);

      $filename = 'contract-document-' . $contract->contract_number . '.pdf';

      return $pdf->download($filename);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'Failed to generate PDF: ' . $e->getMessage()
      ], 500);
    }
  }
}
