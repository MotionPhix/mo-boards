<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Billboard;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BillboardService
{
  public function getFilteredBillboards(
    Company $company,
    array   $filters = [],
    int     $perPage = 15
  ): LengthAwarePaginator
  {
    $query = $company->billboards()
  ->with('company')
      ->withCount(['contracts as active_contracts_count' => function ($query) {
        $query->where('status', 'active');
      }])
      ->with(['contracts' => function ($query) {
        $query->where('status', 'active')
          ->orderBy('contracts.created_at', 'desc')
          ->limit(1);
      }]);

    // Apply filters
    $this->applyFilters($query, $filters);

    // Apply sorting with whitelist
    $allowedSorts = [
      'created_at', 'name', 'code', 'status', 'monthly_rate', 'active_contracts_count'
    ];
    $sortBy = $filters['sort_by'] ?? 'created_at';
    $sortDirection = strtolower($filters['sort_direction'] ?? 'desc');

    if (!in_array($sortBy, $allowedSorts, true)) {
      $sortBy = 'created_at';
    }
    if (!in_array($sortDirection, ['asc', 'desc'], true)) {
      $sortDirection = 'desc';
    }

    $query->orderBy($sortBy, $sortDirection);

    return $query->paginate($perPage)->withQueryString();
  }

  public function getBillboardStats(Company $company): array
  {
    $totalBillboards = $company->billboards()->count();
    $activeBillboards = $company->billboards()->where('status', 'active')->count();
    $maintenanceBillboards = $company->billboards()->where('status', 'maintenance')->count();
    $inactiveBillboards = $company->billboards()->where('status', 'inactive')->count();

    $occupiedBillboards = $company->billboards()
      ->whereHas('contracts', function ($query) {
        $query->where('status', 'active')
          ->where('start_date', '<=', now())
          ->where('end_date', '>=', now());
      })
      ->count();

    $availableBillboards = $activeBillboards - $occupiedBillboards;
    $occupancyRate = $totalBillboards > 0 ? round(($occupiedBillboards / $totalBillboards) * 100, 2) : 0;

    $totalMonthlyRevenue = $company->billboards()
      ->whereHas('contracts', function ($query) {
        $query->where('status', 'active')
          ->where('start_date', '<=', now())
          ->where('end_date', '>=', now());
      })
      ->sum('monthly_rate');

    return [
      'total_billboards' => $totalBillboards,
      'active_billboards' => $activeBillboards,
      'maintenance_billboards' => $maintenanceBillboards,
      'inactive_billboards' => $inactiveBillboards,
      'occupied_billboards' => $occupiedBillboards,
      'available_billboards' => $availableBillboards,
      'occupancy_rate' => $occupancyRate,
      'total_monthly_revenue' => $totalMonthlyRevenue,
    ];
  }

  public function getBillboardsByStatus(Company $company): Collection
  {
    return $company->billboards()
      ->selectRaw('status, COUNT(*) as count')
      ->groupBy('status')
      ->get()
      ->pluck('count', 'status');
  }

  public function getBillboardsBySize(Company $company): Collection
  {
    return $company->billboards()
      ->selectRaw('size, COUNT(*) as count, AVG(monthly_rate) as avg_rate')
      ->groupBy('size')
      ->get();
  }

  public function getAvailableBillboards(
    Company $company,
    ?string $startDate = null,
    ?string $endDate = null
  ): Collection
  {
  $query = $company->billboards()->with('company')->where('status', 'active');

    if ($startDate && $endDate) {
      $query->available($startDate, $endDate);
    }

    return $query->get();
  }

  public function getBillboardRevenue(Billboard $billboard): array
  {
    $activeContracts = $billboard->contracts()
      ->where('status', 'active')
      ->where('start_date', '<=', now())
      ->where('end_date', '>=', now())
      ->get();

    $currentMonthlyRevenue = $activeContracts->sum('monthly_amount');
    $totalContractValue = $activeContracts->sum('total_amount');

    $historicalRevenue = $billboard->contracts()
      ->where('status', 'completed')
      ->sum('total_amount');

    return [
      'current_monthly_revenue' => $currentMonthlyRevenue,
      'total_contract_value' => $totalContractValue,
      'historical_revenue' => $historicalRevenue,
      'total_lifetime_revenue' => $totalContractValue + $historicalRevenue,
      'active_contracts_count' => $activeContracts->count(),
    ];
  }

  public function getBillboardUtilization(Billboard $billboard, int $months = 12): array
  {
    $utilizationData = [];

    for ($i = $months - 1; $i >= 0; $i--) {
      $month = now()->subMonths($i);
      $startOfMonth = $month->copy()->startOfMonth();
      $endOfMonth = $month->copy()->endOfMonth();

      $hasActiveContract = $billboard->contracts()
        ->where('status', 'active')
        ->where(function ($query) use ($startOfMonth, $endOfMonth) {
          $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
            $q->where('start_date', '<=', $endOfMonth)
              ->where('end_date', '>=', $startOfMonth);
          });
        })
        ->exists();

      $utilizationData[] = [
        'month' => $month->format('M Y'),
        'utilized' => $hasActiveContract,
        'revenue' => $hasActiveContract ? $billboard->monthly_rate : 0,
      ];
    }

    return $utilizationData;
  }

  public function searchBillboards(Company $company, string $query): Collection
  {
    return $company->billboards()
  ->with('company')
      ->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('location', 'like', "%{$query}%")
          ->orWhere('code', 'like', "%{$query}%")
          ->orWhere('description', 'like', "%{$query}%");
      })
      ->limit(10)
      ->get();
  }

  public function duplicateBillboard(Billboard $billboard, array $overrides = []): Billboard
  {
  // Ensure company relation is loaded explicitly to avoid lazy load violations
  $billboard->loadMissing('company');
    $data = $billboard->toArray();

    // Remove unique fields
    unset($data['id'], $data['code'], $data['created_at'], $data['updated_at']);

    // Apply overrides
    $data = array_merge($data, $overrides);

    // Ensure unique name
    if (!isset($overrides['name'])) {
      $data['name'] = $data['name'] . ' (Copy)';
    }

  return $billboard->company->billboards()->create($data);
  }

  public function bulkUpdateStatus(Company $company, array $billboardIds, string $status): int
  {
    return $company->billboards()
      ->whereIn('id', $billboardIds)
      ->update(['status' => $status]);
  }

  public function exportBillboards(Company $company, array $filters = []): Collection
  {
    $query = $company->billboards()
      ->with('company')
      ->with(['contracts' => function ($query) {
        $query->where('status', 'active');
      }]);

    $this->applyFilters($query, $filters);

    return $query->get()->map(function ($billboard) {
      return [
        'Code' => $billboard->code,
        'Name' => $billboard->name,
        'Location' => $billboard->location,
        'Size' => $billboard->size,
        'Width' => $billboard->width,
        'Height' => $billboard->height,
        'Monthly Rate' => $billboard->monthly_rate,
        'Status' => $billboard->status,
        'Active Contracts' => $billboard->contracts->count(),
        'Current Revenue' => $billboard->contracts->sum('monthly_amount'),
        'Created At' => $billboard->created_at->format('Y-m-d'),
      ];
    });
  }

  private function applyFilters(Builder|HasMany $query, array $filters): void
  {
    // Search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('location', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    // Status filter
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    // Size filter
    if (!empty($filters['size'])) {
      $query->where('size', $filters['size']);
    }

    // Rate range filter
    if (!empty($filters['min_rate'])) {
      $query->where('monthly_rate', '>=', $filters['min_rate']);
    }

    if (!empty($filters['max_rate'])) {
      $query->where('monthly_rate', '<=', $filters['max_rate']);
    }

    // Availability filter
    if (!empty($filters['availability'])) {
      if ($filters['availability'] === 'available') {
        $query->where('status', 'active')
          ->whereDoesntHave('contracts', function ($q) {
            $q->where('status', 'active')
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
          });
      } elseif ($filters['availability'] === 'occupied') {
        $query->whereHas('contracts', function ($q) {
          $q->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
        });
      }
    }

    // Date range filter
    if (!empty($filters['created_from'])) {
      $query->whereDate('created_at', '>=', $filters['created_from']);
    }

    if (!empty($filters['created_to'])) {
      $query->whereDate('created_at', '<=', $filters['created_to']);
    }
  }
}
