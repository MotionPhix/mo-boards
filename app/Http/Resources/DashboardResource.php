<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\BillboardStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stats = [
            'total_billboards' => [
                'value' => $this->resource['stats']['total_billboards']['value'],
                'change' => $this->resource['stats']['total_billboards']['change'],
                'trend' => $this->resource['stats']['total_billboards']['change'] >= 0 ? 'up' : 'down',
            ],
            'active_contracts' => [
                'value' => $this->resource['stats']['active_contracts']['value'],
                'change' => $this->resource['stats']['active_contracts']['change'],
                'trend' => $this->resource['stats']['active_contracts']['change'] >= 0 ? 'up' : 'down',
            ],
            'occupancy_rate' => [
                'value' => $this->resource['stats']['occupancy_rate']['value'],
                'change' => $this->resource['stats']['occupancy_rate']['change'],
                'trend' => $this->resource['stats']['occupancy_rate']['change'] >= 0 ? 'up' : 'down',
            ],
        ];

        // Only include monthly_revenue if it exists (user has financial permissions)
        if (isset($this->resource['stats']['monthly_revenue'])) {
            $stats['monthly_revenue'] = [
                'value' => number_format($this->resource['stats']['monthly_revenue']['value'], 2),
                'raw_value' => $this->resource['stats']['monthly_revenue']['value'],
                'change' => $this->resource['stats']['monthly_revenue']['change'],
                'trend' => $this->resource['stats']['monthly_revenue']['change'] >= 0 ? 'up' : 'down',
            ];
        }

        // Handle charts conditionally
        $charts = [];

        if (isset($this->resource['charts']['billboard_status'])) {
            $charts['billboard_status'] = [
                'series' => $this->resource['charts']['billboard_status']['series'],
                'labels' => $this->resource['charts']['billboard_status']['labels'],
            ];
        }

        if (isset($this->resource['charts']['contract_status'])) {
            $charts['contract_status'] = [
                'series' => $this->resource['charts']['contract_status']['series'],
                'labels' => $this->resource['charts']['contract_status']['labels'],
            ];
        }

        if (isset($this->resource['charts']['revenue_trend'])) {
            $charts['revenue_trend'] = [
                'categories' => $this->resource['charts']['revenue_trend']['categories'],
                'series' => $this->resource['charts']['revenue_trend']['series'],
            ];
        }

        if (isset($this->resource['charts']['monthly_performance'])) {
            $charts['monthly_performance'] = [
                'categories' => $this->resource['charts']['monthly_performance']['categories'],
                'series' => $this->resource['charts']['monthly_performance']['series'],
            ];
        }

        return [
            'stats' => $stats,
            'charts' => $charts,
            'recent_activities' => collect($this->resource['recent_activities'])->map(function ($activity) {
                $mappedActivity = [
                    'id' => $activity['id'],
                    'type' => $activity['type'],
                    'title' => $activity['title'],
                    'description' => $activity['description'],
                    'billboards_count' => $activity['billboards_count'],
                    'created_at' => $activity['created_at']->format('M d, Y'),
                    'created_at_human' => $activity['created_at']->diffForHumans(),
                    'created_by' => $activity['created_by'],
                ];

                // Only include amount if it exists (user has financial permissions)
                if (isset($activity['amount'])) {
                    $mappedActivity['amount'] = number_format((float) $activity['amount'], 2);
                }

                return $mappedActivity;
            }),
        'top_performing_billboards' => collect($this->resource['top_performing_billboards'])->map(function ($billboard) {
                $mappedBillboard = [
                    'id' => $billboard['id'],
                    'name' => $billboard['name'],
                    'code' => $billboard['code'],
                    'location' => $billboard['location'],
                    'monthly_rate' => number_format((float) $billboard['monthly_rate'], 2),
                    'active_contracts' => $billboard['active_contracts'],
            // Normalize enum/string status to a string value for the client
            'status' => $this->formatBillboardStatus($billboard['status'] ?? null),
            'status_color' => $this->getStatusColor($billboard['status'] ?? null),
                ];

                // Only include total_revenue if it exists (user has financial permissions)
                if (isset($billboard['total_revenue'])) {
                    $mappedBillboard['total_revenue'] = number_format($billboard['total_revenue'], 2);
                }

                return $mappedBillboard;
            }),
            'upcoming_expirations' => collect($this->resource['upcoming_expirations'])->map(function ($contract) {
                return [
                    'id' => $contract['id'],
                    'contract_number' => $contract['contract_number'],
                    'client_name' => $contract['client_name'],
                    'end_date' => $contract['end_date']->format('M d, Y'),
                    'days_remaining' => $contract['days_remaining'],
                    'total_amount' => number_format((float) $contract['total_amount'], 2),
                    'billboards_count' => $contract['billboards_count'],
                    'urgency' => $this->getUrgencyLevel((int) $contract['days_remaining']),
                ];
            }),
            'revenue_breakdown' => $this->transformRevenueBreakdown(),
        ];
    }

    /**
     * Transform revenue breakdown data conditionally
     *
     * @return array<string, mixed>
     */
    private function transformRevenueBreakdown(): array
    {
        // Return empty array if revenue_breakdown not included (user lacks financial permissions)
        if (!isset($this->resource['revenue_breakdown'])) {
            return [];
        }

        $breakdown = $this->resource['revenue_breakdown'];
        $result = [];

        // Only include keys that exist
        if (isset($breakdown['current_month'])) {
            $result['current_month'] = number_format($breakdown['current_month'], 2);
        }

        if (isset($breakdown['by_dimension'])) {
            $result['by_dimension'] = collect($breakdown['by_dimension'])->map(function ($revenue, $dimension) {
                return [
                    'dimension' => $dimension ?: 'Unknown',
                    'revenue' => number_format($revenue, 2),
                    'raw_revenue' => $revenue,
                ];
            })->values();
        }

        if (isset($breakdown['projected_annual'])) {
            $result['projected_annual'] = number_format($breakdown['projected_annual'], 2);
        }

        return $result;
    }

    /**
     * Map billboard status to a UI color token consistent with the model mapping
     * Accepts enum or string; defaults to 'secondary' when unknown.
     */
    private function getStatusColor(BillboardStatus|string|null $status): string
    {
        $value = $status instanceof BillboardStatus ? $status->value : (string) $status;

        return match ($value) {
            'active' => 'success',
            'available' => 'secondary',
            'maintenance' => 'destructive',
            'removed' => 'outline',
            default => 'secondary',
        };
    }

    /**
     * Normalize billboard status to string for the API response
     */
    private function formatBillboardStatus(BillboardStatus|string|null $status): string
    {
        return $status instanceof BillboardStatus ? $status->value : (string) ($status ?? '');
    }

    private function getUrgencyLevel(int $daysRemaining): string
    {
        if ($daysRemaining <= 7) {
            return 'high';
        } elseif ($daysRemaining <= 14) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}
