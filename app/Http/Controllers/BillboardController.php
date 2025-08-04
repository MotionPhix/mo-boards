<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillboardRequest;
use App\Http\Requests\UpdateBillboardRequest;
use App\Http\Resources\BillboardResource;
use App\Models\Billboard;
use App\Services\BillboardService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class BillboardController extends Controller
{
  use AuthorizesRequests;

  public function __construct(
    private readonly BillboardService $billboardService
  )
  {}

  public function index(Request $request): Response
  {
    $this->authorize('billboards.view_any', Billboard::class);

    $user = Auth::user();
    $company = $user->currentCompany;

    if (!$company) {
      return Inertia::render('companies/Select', [
        'companies' => $user->companies()->get(),
      ]);
    }

    // Get filters from request
    $filters = $request->only([
      'search', 'status', 'size', 'availability',
      'min_rate', 'max_rate', 'created_from', 'created_to',
      'sort_by', 'sort_direction',
    ]);

    // Get filtered billboards
    $billboards = $this->billboardService->getFilteredBillboards($company, $filters);

    // Get billboard stats
    $stats = $this->billboardService->getBillboardStats($company);

    // Get available sizes for filter (generated from width x height combinations)
    $availableSizes = $company->billboards()
      ->whereNotNull('width')
      ->whereNotNull('height')
      ->get()
      ->map(function ($billboard) {
        return $billboard->width . ' x ' . $billboard->height;
      })
      ->unique()
      ->sort()
      ->values();

    return Inertia::render('billboards/Index', [
      'billboards' => BillboardResource::collection($billboards),
      'stats' => $stats,
      'filters' => $filters,
      'available_sizes' => $availableSizes,
      'companies' => $user->companies()->get(),
      'company' => [
        'id' => $company->id,
        'name' => $company->name,
      ],
      'permissions' => [
        'can_create' => $user->can('create', Billboard::class),
        'can_export' => $user->can('exportData', Billboard::class),
        'can_bulk_update' => $user->can('bulkUpdate', Billboard::class),
      ],
    ]);
  }

  public function create(): Response
  {
    $this->authorize('create', Billboard::class);

    $user = Auth::user();
    $company = $user->currentCompany;

    if (!$company) {
      return redirect()->route('companies.index')
        ->with('error', 'Please select a company first.');
    }

    return Inertia::render('billboards/Create', [
      'company' => [
        'id' => $company->id,
        'name' => $company->name,
      ],
    ]);
  }

  public function store(StoreBillboardRequest $request): RedirectResponse
  {
    $this->authorize('create', Billboard::class);

    $user = Auth::user();
    $company = $user->currentCompany;

    if (!$company) {
      return redirect()->route('companies.index')
        ->with('error', 'Please select a company first.');
    }

    $billboard = $company->billboards()->create($request->validated());

    return redirect()->route('billboards.index')
      ->with('success', "Billboard '{$billboard->name}' created successfully!");
  }

  public function show(Billboard $billboard): Response
  {
    $this->authorize('view', $billboard);

    $billboard->load(['media', 'contracts' => function ($query) {
      $query->latest()->limit(5);
    }]);

    // Get billboard revenue data
    $revenueData = $this->billboardService->getBillboardRevenue($billboard);

    // Get utilization data
    $utilizationData = $this->billboardService->getBillboardUtilization($billboard);

    $user = Auth::user();

    return Inertia::render('billboards/Show', [
      'billboard' => new BillboardResource($billboard),
      'revenue_data' => $revenueData,
      'utilization_data' => $utilizationData,
      'permissions' => [
        'can_update' => $user->can('update', $billboard),
        'can_delete' => $user->can('delete', $billboard),
        'can_duplicate' => $user->can('duplicate', $billboard),
        'can_view_analytics' => $user->can('viewAnalytics', $billboard),
        'can_manage_media' => $user->can('manageMedia', $billboard),
      ],
    ]);
  }

  public function edit(Billboard $billboard): Response
  {
    $this->authorize('update', $billboard);

    return Inertia::render('billboards/Edit', [
      'billboard' => new BillboardResource($billboard),
    ]);
  }

  public function update(UpdateBillboardRequest $request, Billboard $billboard): RedirectResponse
  {
    $this->authorize('update', $billboard);

    $billboard->update($request->validated());

    return redirect()->route('billboards.show', $billboard)
      ->with('success', "Billboard '{$billboard->name}' updated successfully!");
  }

  public function destroy(Billboard $billboard): RedirectResponse
  {
    $this->authorize('delete', $billboard);

    // Check if billboard has active contracts
    $activeContracts = $billboard->contracts()->where('status', 'active')->count();

    if ($activeContracts > 0) {
      return redirect()->back()
        ->with('error', 'Cannot delete billboard with active contracts.');
    }

    $name = $billboard->name;
    $billboard->delete();

    return redirect()->route('billboards.index')
      ->with('success', "Billboard '{$name}' deleted successfully!");
  }

  public function duplicate(Billboard $billboard): RedirectResponse
  {
    $this->authorize('duplicate', $billboard);

    $newBillboard = $this->billboardService->duplicateBillboard($billboard);

    return redirect()->route('billboards.edit', $newBillboard)
      ->with('success', 'Billboard duplicated successfully! Please review and update the details.');
  }

  public function bulkUpdate(Request $request): JsonResponse
  {
    $this->authorize('bulkUpdate', Billboard::class);

    $request->validate([
      'billboard_ids' => 'required|array',
      'billboard_ids.*' => 'exists:billboards,id',
      'action' => 'required|in:activate,deactivate,maintenance',
    ]);

    $user = Auth::user();
    $company = $user->currentCompany;

    $status = match ($request->action) {
      'activate' => 'active',
      'deactivate' => 'inactive',
      'maintenance' => 'maintenance',
    };

    $updated = $this->billboardService->bulkUpdateStatus(
      $company,
      $request->billboard_ids,
      $status
    );

    return response()->json([
      'message' => "Successfully updated {$updated} billboards.",
      'updated_count' => $updated,
    ]);
  }

  public function search(Request $request): JsonResponse
  {
    $this->authorize('viewAny', Billboard::class);

    $request->validate([
      'query' => 'required|string|min:2|max:100',
    ]);

    $user = Auth::user();
    $company = $user->currentCompany;

    if (!$company) {
      return response()->json(['billboards' => []]);
    }

    $billboards = $this->billboardService->searchBillboards($company, $request->query);

    return response()->json([
      'billboards' => BillboardResource::collection($billboards),
    ]);
  }

  public function export(Request $request): JsonResponse
  {
    $this->authorize('exportData', Billboard::class);

    $user = Auth::user();
    $company = $user->currentCompany;

    if (!$company) {
      return response()->json(['error' => 'No company selected'], 400);
    }

    $filters = $request->only([
      'search', 'status', 'size', 'availability',
      'min_rate', 'max_rate', 'created_from', 'created_to',
    ]);

    $data = $this->billboardService->exportBillboards($company, $filters);

    return response()->json([
      'data' => $data,
      'filename' => 'billboards_' . now()->format('Y-m-d_H-i-s') . '.csv',
    ]);
  }
}
