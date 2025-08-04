<?php

declare(strict_types=1);

namespace App\Http\Resources;

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
        return [
            'stats' => [
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
                'monthly_revenue' => [
                    'value' => number_format($this->resource['stats']['monthly_revenue']['value'], 2),
                    'raw_value' => $this->resource['stats']['monthly_revenue']['value'],
                    'change' => $this->resource['stats']['monthly_revenue']['change'],
                    'trend' => $this->resource['stats']['monthly_revenue']['change'] >= 0 ? 'up' : 'down',
                ],
                'occupancy_rate' => [
                    'value' => $this->resource['stats']['occupancy_rate']['value'],
                    'change' => $this->resource['stats']['occupancy_rate']['change'],
                    'trend' => $this->resource['stats']['occupancy_rate']['change'] >= 0 ? 'up' : 'down',
                ],
            ],
            'charts' => [
                'revenue_trend' => [
                    'categories' => $this->resource['charts']['revenue_trend']['categories'],
                    'series' => $this->resource['charts']['revenue_trend']['series'],
                ],
                'billboard_status' => [
                    'series' => $this->resource['charts']['billboard_status']['series'],
                    'labels' => $this->resource['charts']['billboard_status']['labels'],
                ],
                'contract_status' => [
                    'series' => $this->resource['charts']['contract_status']['series'],
                    'labels' => $this->resource['charts']['contract_status']['labels'],
                ],
                'monthly_performance' => [
                    'categories' => $this->resource['charts']['monthly_performance']['categories'],
                    'series' => $this->resource['charts']['monthly_performance']['series'],
                ],
            ],
            'recent_activities' => $this->resource['recent_activities']->map(function ($activity) {
                return [
                    'id' => $activity['id'],
                    'type' => $activity['type'],
                    'title' => $activity['title'],
                    'description' => $activity['description'],
                    'amount' => number_format((float) $activity['amount'], 2),
                    'billboards_count' => $activity['billboards_count'],
                    'created_at' => $activity['created_at']->format('M d, Y'),
                    'created_at_human' => $activity['created_at']->diffForHumans(),
                    'created_by' => $activity['created_by'],
                ];
            }),
            'top_performing_billboards' => $this->resource['top_performing_billboards']->map(function ($billboard) {
                return [
                    'id' => $billboard['id'],
                    'name' => $billboard['name'],
                    'code' => $billboard['code'],
                    'location' => $billboard['location'],
                    'monthly_rate' => number_format((float) $billboard['monthly_rate'], 2),
                    'active_contracts' => $billboard['active_contracts'],
                    'total_revenue' => number_format($billboard['total_revenue'], 2),
                    'status' => $billboard['status'],
                    'status_color' => $this->getStatusColor($billboard['status']),
                ];
            }),
            'upcoming_expirations' => $this->resource['upcoming_expirations']->map(function ($contract) {
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
            'revenue_breakdown' => [
                'current_month' => number_format($this->resource['revenue_breakdown']['current_month'], 2),
                'by_dimension' => collect($this->resource['revenue_breakdown']['by_dimension'])->map(function ($revenue, $dimension) {
                    return [
                        'dimension' => $dimension ?: 'Unknown',
                        'revenue' => number_format($revenue, 2),
                        'raw_revenue' => $revenue,
                    ];
                })->values(),
                'projected_annual' => number_format($this->resource['revenue_breakdown']['projected_annual'], 2),
            ],
        ];
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'maintenance' => 'yellow',
            'inactive' => 'red',
            default => 'gray',
        };
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
