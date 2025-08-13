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
  {
  // Dependencies injected via constructor.
  }

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








}
