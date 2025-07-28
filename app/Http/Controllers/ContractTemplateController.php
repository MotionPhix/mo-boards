<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractTemplateController extends Controller
{
  public function index(): Response
  {
    $company = auth()->user()->currentCompany();

    $templates = ContractTemplate::where('company_id', $company->id)
      ->withCount('contracts')
      ->orderBy('created_at', 'desc')
      ->get();

    return Inertia::render('ContractTemplates/Index', [
      'templates' => $templates
    ]);
  }

  public function create(): Response
  {
    return Inertia::render('ContractTemplates/Create');
  }

  public function store(Request $request)
  {
    $company = $request->user()->currentCompany;

    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'default_terms' => 'required|array',
      'custom_fields' => 'nullable|array',
      'is_active' => 'boolean',
    ]);

    ContractTemplate::create([
      ...$validated,
      'company_id' => $company->id,
    ]);

    return redirect()->route('contract-templates.index')
      ->with('success', 'Template created successfully.');
  }

  public function show(ContractTemplate $contractTemplate): Response
  {
    $contractTemplate->load(['contracts' => function ($query) {
      $query->latest()->limit(10);
    }]);

    return Inertia::render('ContractTemplates/Show', [
      'template' => $contractTemplate
    ]);
  }

  public function edit(ContractTemplate $contractTemplate): Response
  {
    return Inertia::render('ContractTemplates/Edit', [
      'template' => $contractTemplate
    ]);
  }

  public function update(Request $request, ContractTemplate $contractTemplate)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'default_terms' => 'required|array',
      'custom_fields' => 'nullable|array',
      'is_active' => 'boolean',
    ]);

    $contractTemplate->update($validated);

    return redirect()->route('contract-templates.index')
      ->with('success', 'Template updated successfully.');
  }

  public function destroy(ContractTemplate $contractTemplate)
  {
    $contractTemplate->delete();

    return redirect()->route('contract-templates.index')
      ->with('success', 'Template deleted successfully.');
  }
}
