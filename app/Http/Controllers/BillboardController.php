<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillboardRequest;
use App\Http\Requests\UpdateBillboardRequest;
use App\Http\Resources\BillboardResource;
use App\Models\Billboard;
use App\Services\BillboardService;
use App\Enums\BillboardStatus;
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

  public function create(): Response|RedirectResponse
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
  'statuses' => BillboardStatus::options(),
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

  // Exclude 'images' from mass assignment; files are handled via Media Library
  $data = $request->safe()->except(['images']);
  $billboard = $company->billboards()->create($data);

    // Attach uploaded images (if any)
    if (request()->hasFile('images')) {
      foreach ((array) request()->file('images') as $image) {
        $billboard->addMedia($image)->toMediaCollection('images');
      }
    }

    return redirect()->route('billboards.index')
      ->with('success', "Billboard '{$billboard->name}' created successfully!");
  }

  public function show(Billboard $billboard): Response
  {
    $this->authorize('view', $billboard);

  $billboard->load(['company','media', 'contracts' => function ($query) {
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

  $billboard->loadMissing('company', 'media')
    ->loadCount(['contracts as active_contracts_count' => function ($query) {
      $query->where('status', 'active');
    }]);

  // Nearby/other billboards for clustering (same company, with coordinates)
  $nearby = $billboard->company
    ? $billboard->company->billboards()
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->where('id', '!=', $billboard->id)
        ->select('id', 'name', 'code', 'latitude', 'longitude')
        ->limit(500)
        ->get()
        ->map(fn ($b) => [
          'id' => $b->id,
          'name' => $b->name,
          'code' => $b->code,
          'latitude' => (float) $b->latitude,
          'longitude' => (float) $b->longitude,
        ])
    : collect();

  return Inertia::render('billboards/Edit', [
      'billboard' => new BillboardResource($billboard),
      'nearby_markers' => $nearby,
  'statuses' => BillboardStatus::options(),
    ]);
  }

  public function update(UpdateBillboardRequest $request, Billboard $billboard): RedirectResponse
  {
    $this->authorize('update', $billboard);

  // Exclude 'images' from mass assignment; files are handled via Media Library
  $data = $request->safe()->except(['images']);
  $billboard->update($data);

    // Attach any newly uploaded images
    if (request()->hasFile('images')) {
      foreach ((array) request()->file('images') as $image) {
        $billboard->addMedia($image)->toMediaCollection('images');
      }
    }

    return redirect()->route('billboards.show', $billboard)
      ->with('success', "Billboard '{$billboard->name}' updated successfully!");
  }

  public function deleteMedia(Request $request, Billboard $billboard): RedirectResponse
  {
    $this->authorize('manageMedia', $billboard);

    $request->validate(['media_id' => 'required|integer']);
    $media = $billboard->media()->where('id', $request->input('media_id'))->firstOrFail();
    $media->delete();

    return back()->with('success', 'Image removed.');
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
      'action' => 'required|in:activate,set_available,maintenance,remove',
    ]);

    $user = Auth::user();
    $company = $user->currentCompany;

    // If any selected billboard is currently in maintenance, only company owners can change its status
    $hasMaintenance = Billboard::whereIn('id', $request->billboard_ids)
      ->where('status', BillboardStatus::MAINTENANCE->value)
      ->exists();
    if ($hasMaintenance && !($user?->hasRole('company_owner'))) {
      return response()->json([
        'message' => 'Only a company owner can change status while a billboard is in maintenance.',
      ], 403);
    }

    $status = match ($request->action) {
      'activate' => BillboardStatus::ACTIVE->value,
      'set_available' => BillboardStatus::AVAILABLE->value,
      'maintenance' => BillboardStatus::MAINTENANCE->value,
      'remove' => BillboardStatus::REMOVED->value,
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

  $billboards = $this->billboardService->searchBillboards($company, $request->input('query'));

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
