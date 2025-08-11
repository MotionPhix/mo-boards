<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\BillboardStatus;
use App\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillboardResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $company = $this->company;
    $currency = $company->currency ?? 'USD';
    $currencySymbol = CurrencyHelper::getSymbol($currency);

  $activeContractsCount = $this->getActiveContractsCount();

  // Normalize status to string in case the model casts it to an enum
  $statusValue = $this->status instanceof BillboardStatus ? $this->status->value : (string) $this->status;

  return [
      'id' => $this->id,
      'uuid' => $this->uuid,
      'code' => $this->code,
      'name' => $this->name,
      'location' => $this->location,
      'coordinates' => [
        'latitude' => $this->latitude,
        'longitude' => $this->longitude,
      ],
      'dimensions' => [
        'width' => $this->width,
        'height' => $this->height,
        'size' => $this->size,
        'area' => $this->width && $this->height ? round($this->width * $this->height, 2) : null,
      ],
      'pricing' => [
        'monthly_rate' => $this->monthly_rate,
        'currency' => $currency,
        'currency_symbol' => $currencySymbol,
        'formatted_rate' => $this->monthly_rate ? CurrencyHelper::format((float) $this->monthly_rate, $currency) : null,
        'annual_rate' => $this->monthly_rate ? $this->monthly_rate * 12 : null,
        'formatted_annual_rate' => $this->monthly_rate ? CurrencyHelper::format((float) $this->monthly_rate * 12, $currency) : null,
      ],
      'status' => [
        'current' => $statusValue,
        'label' => $this->status instanceof BillboardStatus
          ? $this->status->label()
          : (BillboardStatus::tryFrom($statusValue)?->label() ?? ucfirst($statusValue)),
        'color' => $this->getStatusColor(),
        'can_edit' => $statusValue !== BillboardStatus::MAINTENANCE->value
          || (request()->user()?->hasRole('company_owner') ?? false),
      ],
      'description' => $this->description,
      'contracts' => [
        'active_count' => $activeContractsCount,
        'is_occupied' => $this->isCurrentlyOccupied(),
        'current_contract' => $this->when(
          $this->relationLoaded('contracts') && $this->contracts->isNotEmpty(),
          function () {
            $activeContract = $this->contracts->first();
            return $activeContract ? [
              'id' => $activeContract->id,
              'client_name' => $activeContract->client_name,
              'start_date' => $activeContract->start_date->format('M d, Y'),
              'end_date' => $activeContract->end_date->format('M d, Y'),
              'monthly_amount' => '$' . number_format((float) $activeContract->monthly_amount, 2),
            ] : null;
          }
        ),
      ],
      'media' => $this->when(
        $this->relationLoaded('media'),
        function () {
          return $this->media->map(function ($media) {
            return [
              'id' => $media->id,
              'name' => $media->name,
              'url' => $media->getUrl(),
              'preview_url' => method_exists($media, 'hasGeneratedConversion') && $media->hasGeneratedConversion('preview')
                ? $media->getUrl('preview')
                : $media->getUrl(),
              'type' => $media->mime_type,
              'size' => $this->formatFileSize($media->size),
              'collection' => $media->collection_name,
            ];
          });
        }
      ),
      'images' => $this->when(
        $this->relationLoaded('media'),
        fn () => $this->getMedia('images')->map(fn ($m) => [
          'id' => $m->id,
          'name' => $m->name,
          'url' => $m->getUrl(),
          'preview_url' => method_exists($m, 'hasGeneratedConversion') && $m->hasGeneratedConversion('preview') ? $m->getUrl('preview') : $m->getUrl(),
          'type' => $m->mime_type,
          'size' => $this->formatFileSize($m->size),
        ])),
      'documents' => $this->when(
        $this->relationLoaded('media'),
        fn () => $this->getMedia('documents')->map(fn ($m) => [
          'id' => $m->id,
          'name' => $m->name,
          'url' => $m->getUrl(),
          'type' => $m->mime_type,
          'size' => $this->formatFileSize($m->size),
        ])),
      'performance' => $this->when(
        $request->routeIs('billboards.show'),
        function () {
          return [
            'utilization_rate' => $this->calculateUtilizationRate(),
            'total_revenue' => $this->calculateTotalRevenue(),
            'average_contract_duration' => $this->calculateAverageContractDuration(),
          ];
        }
      ),
      'company' => [
        'id' => $company->id,
        'name' => $company->name,
        'currency' => $currency,
        'timezone' => $company->timezone ?? 'UTC',
        'date_format' => $company->date_format ?? 'Y-m-d',
      ],
      'created_at' => $this->created_at?->setTimezone($company->timezone ?? 'UTC')->format($company->date_format ?? 'Y-m-d'),
      'updated_at' => $this->updated_at?->setTimezone($company->timezone ?? 'UTC')->format($company->date_format ?? 'Y-m-d'),
      'actions' => [
        'can_view' => true,
        'can_edit' => $statusValue !== BillboardStatus::MAINTENANCE->value
          || (request()->user()?->hasRole('company_owner') ?? false),
        'can_delete' => $activeContractsCount === 0,
        'can_duplicate' => true,
      ],
    ];
  }

  private function getActiveContractsCount(): int
  {
    // Prefer preloaded alias from withCount to avoid extra queries
    try {
      $attributes = method_exists($this->resource, 'getAttributes')
        ? $this->resource->getAttributes()
        : [];
      if (array_key_exists('active_contracts_count', $attributes)) {
        return (int) $attributes['active_contracts_count'];
      }
    } catch (\Throwable $e) {
      // Fall through to DB count
    }

    // Fallback: compute directly
    try {
      return (int) $this->resource->contracts()
        ->where('status', 'active')
        ->count();
    } catch (\Throwable $e) {
      return 0;
    }
  }

  private function getStatusColor(): string
  {
    $statusValue = $this->status instanceof BillboardStatus ? $this->status->value : (string) $this->status;
    return match ($statusValue) {
      'active' => 'green',
      'available' => 'blue',
      'maintenance' => 'yellow',
      'removed' => 'gray',
      default => 'gray',
    };
  }

  private function isCurrentlyOccupied(): bool
  {
    if (!$this->relationLoaded('contracts')) {
      return false;
    }

    return $this->contracts->where('status', 'active')
      ->where('start_date', '<=', now())
      ->where('end_date', '>=', now())
      ->isNotEmpty();
  }

  private function calculateUtilizationRate(): float
  {
    $totalDays = $this->created_at->diffInDays(now());
    if ($totalDays === 0) return 0;

    $occupiedDays = $this->contracts()
      ->where('status', 'active')
      ->get()
      ->sum(function ($contract) {
        $start = max($contract->start_date, $this->created_at);
        $end = min($contract->end_date, now());
        return $start->diffInDays($end);
      });

    return round(($occupiedDays / $totalDays) * 100, 2);
  }

  private function calculateTotalRevenue(): float
  {
    try {
      $sum = $this->contracts()
        ->whereIn('status', ['active', 'completed'])
        ->sum('total_amount');

      return (float) $sum;
    } catch (\Throwable $e) {
      return 0.0;
    }
  }

  private function calculateAverageContractDuration(): int
  {
    $contracts = $this->contracts()
      ->whereIn('status', ['active', 'completed'])
      ->get();

    if ($contracts->isEmpty()) return 0;

    $totalDays = $contracts->sum(function ($contract) {
      return $contract->start_date->diffInDays($contract->end_date);
    });

  return (int) round($totalDays / $contracts->count());
  }

  private function formatFileSize(int $bytes): string
  {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
  }
}
