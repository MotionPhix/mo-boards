<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Billboard;
use App\Models\Contract;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
  public function index(Request $request): Response
  {
    $company = $request->user()->currentCompany;

    $contracts = Contract::with(['billboards', 'template', 'createdBy'])
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

    return Inertia::render('Contracts/Index', [
      'contracts' => $contracts,
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
    $company = auth()->user()->currentCompany;

    return Inertia::render('Contracts/Create', [
      'templates' => ContractTemplate::where('company_id', $company->id)
        ->active()
        ->get(),
      'billboards' => Billboard::where('company_id', $company->id)
        ->get(['id', 'name', 'code', 'location', 'monthly_rate']),
    ]);
  }

  public function store(Request $request)
  {
    $company = $request->user()->currentCompany;

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

    $contract = Contract::create([
      ...$validated,
      'company_id' => $company->id,
      'created_by' => $request->user()->id,
      'total_amount' => $totalAmount,
      'monthly_amount' => $totalRate,
    ]);

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

    $contract->load(['billboards', 'template', 'createdBy', 'payments']);

    return Inertia::render('Contracts/Show', [
      'contract' => $contract
    ]);
  }

  public function edit(Contract $contract): Response
  {
    $this->authorize('update', $contract);

    $company = auth()->user()->currentCompany;

    $contract->load('billboards');

    return Inertia::render('Contracts/Edit', [
      'contract' => $contract,
      'templates' => ContractTemplate::where('company_id', $company->id)
        ->active()
        ->get(),
      'billboards' => Billboard::where('company_id', $company->id)
        ->get(['id', 'name', 'code', 'location', 'monthly_rate']),
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
      'billboards' => 'required|array|min:1',
      'billboards.*.id' => 'required|exists:billboards,id',
      'billboards.*.rate' => 'required|numeric|min:0',
      'billboards.*.notes' => 'nullable|string',
      'terms_and_conditions' => 'required|array',
      'custom_fields_data' => 'nullable|array',
      'notes' => 'nullable|string',
      'status' => 'sometimes|in:draft,pending,active,completed,cancelled',
    ]);

    // Recalculate amounts
    $totalRate = collect($validated['billboards'])->sum('rate');
    $months = now()->parse($validated['start_date'])->diffInMonths($validated['end_date']) + 1;
    $totalAmount = $totalRate * $months;

    $contract->update([
      ...$validated,
      'total_amount' => $totalAmount,
      'monthly_amount' => $totalRate,
    ]);

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
}
