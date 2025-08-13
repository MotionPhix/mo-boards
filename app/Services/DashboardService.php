<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Billboard;
use App\Models\Company;
use App\Models\Contract;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(Company $company, ?User $user = null, array $context = []): array
    {
        $canViewFinancial = $context['can_view_financial'] ?? false;
        $canViewAdvancedAnalytics = $context['can_view_advanced_analytics'] ?? false;
        $userRole = $context['user_role'] ?? 'viewer';

        return [
            'stats' => $this->getStats($company, $canViewFinancial),
            'charts' => $canViewAdvancedAnalytics ? $this->getChartData($company, $canViewFinancial) : [],
            'recent_activities' => $this->getRecentActivities($company, $canViewFinancial, $userRole),
            'top_performing_billboards' => $canViewAdvancedAnalytics ? $this->getTopPerformingBillboards($company, $canViewFinancial) : [],
            'upcoming_expirations' => $this->getUpcomingExpirations($company),
            'revenue_breakdown' => $canViewFinancial ? $this->getRevenueBreakdown($company) : [],
        ];
    }

    private function getStats(Company $company, bool $canViewFinancial = false): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Current stats
        $totalBillboards = $company->billboards()->count();
        $activeContracts = $company->contracts()->active()->count();
        $occupancyRate = $this->calculateOccupancyRate($company);

        // Revenue stats only for authorized users
        $monthlyRevenue = $canViewFinancial ? $this->getMonthlyRevenue($company, $currentMonth) : 0;
        $lastMonthRevenue = $canViewFinancial ? $this->getMonthlyRevenue($company, $lastMonth) : 0;

        // Previous month stats for comparison
        $lastMonthActiveContracts = $company->contracts()
            ->where('status', 'active')
            ->whereDate('created_at', '>=', $lastMonth)
            ->whereDate('created_at', '<', $currentMonth)
            ->count();

        $stats = [
            'total_billboards' => [
                'value' => $totalBillboards,
                'change' => $this->calculatePercentageChange(
                    $totalBillboards,
                    $company->billboards()->whereDate('created_at', '<', $currentMonth)->count()
                ),
            ],
            'active_contracts' => [
                'value' => $activeContracts,
                'change' => $this->calculatePercentageChange($activeContracts, $lastMonthActiveContracts),
            ],
            'occupancy_rate' => [
                'value' => $occupancyRate,
                'change' => $this->calculateOccupancyRateChange($company),
            ],
        ];

        // Add revenue stats only if user can view financial data
        if ($canViewFinancial) {
            $stats['monthly_revenue'] = [
                'value' => $monthlyRevenue,
                'change' => $this->calculatePercentageChange($monthlyRevenue, $lastMonthRevenue),
            ];
        }

        return $stats;
    }

    private function getChartData(Company $company, bool $canViewFinancial = false): array
    {
        $chartData = [
            'billboard_status' => $this->getBillboardStatusData($company),
            'contract_status' => $this->getContractStatusData($company),
        ];

        // Add financial charts only for authorized users
        if ($canViewFinancial) {
            $chartData['revenue_trend'] = $this->getRevenueTrendData($company);
            $chartData['monthly_performance'] = $this->getMonthlyPerformanceData($company);
        }

        return $chartData;
    }

    private function getRevenueTrendData(Company $company): array
    {
        $months = collect();
        $revenues = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $months->push($date->format('M Y'));
            $revenues->push($this->getMonthlyRevenue($company, $date));
        }

        return [
            'categories' => $months->toArray(),
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $revenues->toArray(),
                ],
            ],
        ];
    }

    private function getBillboardStatusData(Company $company): array
    {
        $totalBillboards = $company->billboards()->count();
        $occupiedBillboards = $company->billboards()
            ->whereHas('contracts', function ($query) {
                $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->count();

        $maintenanceBillboards = $company->billboards()
            ->where('status', 'maintenance')
            ->count();

        $availableBillboards = $totalBillboards - $occupiedBillboards - $maintenanceBillboards;

        return [
            'series' => [$occupiedBillboards, $availableBillboards, $maintenanceBillboards],
            'labels' => ['Occupied', 'Available', 'Maintenance'],
        ];
    }

    private function getContractStatusData(Company $company): array
    {
        $statusCounts = $company->contracts()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'series' => array_values($statusCounts),
            'labels' => array_keys($statusCounts),
        ];
    }

    private function getMonthlyPerformanceData(Company $company): array
    {
        $months = collect();
        $newContracts = collect();
        $revenue = collect();

        for ($i = 5; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $months->push($startDate->format('M'));

            $monthlyNewContracts = $company->contracts()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $monthlyRevenue = $this->getMonthlyRevenue($company, $startDate);

            $newContracts->push($monthlyNewContracts);
            $revenue->push($monthlyRevenue);
        }

        return [
            'categories' => $months->toArray(),
            'series' => [
                [
                    'name' => 'New Contracts',
                    'type' => 'column',
                    'data' => $newContracts->toArray(),
                ],
                [
                    'name' => 'Revenue',
                    'type' => 'line',
                    'data' => $revenue->toArray(),
                ],
            ],
        ];
    }

    private function getRecentActivities(Company $company, bool $canViewFinancial = false, string $userRole = 'viewer'): Collection
    {
        $query = $company->contracts()
            ->with(['billboards', 'createdBy'])
            ->latest()
            ->limit(10);

        // Viewers might see fewer activities
        if ($userRole === 'viewer') {
            $query->limit(5);
        }

        return $query->get()
            ->map(function ($contract) use ($canViewFinancial) {
                $activity = [
                    'id' => $contract->id,
                    'type' => 'contract',
                    'title' => "New contract with {$contract->client_name}",
                    'description' => "Contract {$contract->contract_number} created",
                    'billboards_count' => $contract->billboards->count(),
                    'created_at' => $contract->created_at,
                    'created_by' => $contract->createdBy->name,
                ];

                // Only include financial data if user can view it
                if ($canViewFinancial) {
                    $activity['amount'] = $contract->total_amount;
                }

                return $activity;
            });
    }

    private function getTopPerformingBillboards(Company $company, bool $canViewFinancial = false): Collection
    {
        return $company->billboards()
            ->withCount(['contracts as active_contracts_count' => function ($query) {
                $query->where('status', 'active');
            }])
            ->with(['contracts' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('active_contracts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($billboard) use ($canViewFinancial) {
                $data = [
                    'id' => $billboard->id,
                    'name' => $billboard->name,
                    'code' => $billboard->code,
                    'location' => $billboard->location,
                    'monthly_rate' => $billboard->monthly_rate,
                    'active_contracts' => $billboard->active_contracts_count,
                    'status' => $billboard->status,
                ];

                // Only include revenue data if user can view financial information
                if ($canViewFinancial) {
                    $data['total_revenue'] = $billboard->contracts
                        ->where('status', 'active')
                        ->sum('total_amount');
                }

                return $data;
            });
    }

    private function getUpcomingExpirations(Company $company): Collection
    {
        return $company->contracts()
            ->with(['billboards'])
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'client_name' => $contract->client_name,
                    'end_date' => $contract->end_date,
                    'days_remaining' => now()->diffInDays($contract->end_date),
                    'total_amount' => $contract->total_amount,
                    'billboards_count' => $contract->billboards->count(),
                ];
            });
    }

    private function getRevenueBreakdown(Company $company): array
    {
        $currentMonth = now()->startOfMonth();
        $currentMonthRevenue = $this->getMonthlyRevenue($company, $currentMonth);

        // Revenue by billboard dimensions (width x height)
        $revenueByDimension = $company->billboards()
            ->join('contract_billboards', 'billboards.id', '=', 'contract_billboards.billboard_id')
            ->join('contracts', 'contract_billboards.contract_id', '=', 'contracts.id')
            ->where('contracts.status', 'active')
            ->select(
                'billboards.width',
                'billboards.height',
                DB::raw('SUM(contract_billboards.rate) as revenue')
            )
            ->groupBy('billboards.width', 'billboards.height')
            ->get()
            ->mapWithKeys(function ($item) {
                $dimension = $item->width . 'm x ' . $item->height . 'm';
                return [$dimension => (float) $item->revenue];
            })
            ->toArray();

        return [
            'current_month' => $currentMonthRevenue,
            'by_dimension' => $revenueByDimension,
            'projected_annual' => $currentMonthRevenue * 12,
        ];
    }

    private function getMonthlyRevenue(Company $company, CarbonInterface $month): float
    {
        $revenue = $company->contracts()
            ->where('status', 'active')
            ->where('start_date', '<=', $month->endOfMonth())
            ->where('end_date', '>=', $month->startOfMonth())
            ->sum('monthly_amount');

        return (float) $revenue;
    }

    private function calculateOccupancyRate(Company $company): float
    {
        $totalBillboards = $company->billboards()->count();

        if ($totalBillboards === 0) {
            return 0;
        }

        $occupiedBillboards = $company->billboards()
            ->whereHas('contracts', function ($query) {
                $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->count();

        return round(($occupiedBillboards / $totalBillboards) * 100, 2);
    }

    private function calculateOccupancyRateChange(Company $company): float
    {
        $currentRate = $this->calculateOccupancyRate($company);

        // Calculate last month's occupancy rate
        $lastMonth = now()->subMonth();
        $totalBillboards = $company->billboards()
            ->whereDate('created_at', '<=', $lastMonth)
            ->count();

        if ($totalBillboards === 0) {
            return 0;
        }

        $lastMonthOccupied = $company->billboards()
            ->whereDate('created_at', '<=', $lastMonth)
            ->whereHas('contracts', function ($query) use ($lastMonth) {
                $query->where('status', 'active')
                    ->where('start_date', '<=', $lastMonth)
                    ->where('end_date', '>=', $lastMonth);
            })
            ->count();

        $lastMonthRate = $totalBillboards > 0 ? ($lastMonthOccupied / $totalBillboards) * 100 : 0;

        return round($currentRate - $lastMonthRate, 2);
    }

    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
