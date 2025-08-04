<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $company = $this->relationLoaded('company') ? $this->company : null;
        $contractCurrency = $this->currency ?? $company?->currency ?? 'USD';
        $companyCurrency = $company?->currency ?? 'USD';
        $currencySymbol = CurrencyHelper::getSymbol($contractCurrency);
        $timezone = $company?->timezone ?? 'UTC';
        $dateFormat = $company?->date_format ?? 'Y-m-d';

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'contract_number' => $this->contract_number,
            'status' => [
                'current' => $this->status,
                'label' => ucfirst($this->status),
                'color' => $this->getStatusColor(),
            ],
            'client' => [
                'name' => $this->client_name,
                'email' => $this->client_email,
                'phone' => $this->client_phone,
                'company' => $this->client_company,
                'address' => $this->client_address,
            ],
            'dates' => [
                'start_date' => $this->start_date?->setTimezone($timezone)->format($dateFormat),
                'end_date' => $this->end_date?->setTimezone($timezone)->format($dateFormat),
                'signed_at' => $this->signed_at?->setTimezone($timezone)->format($dateFormat . ' ' . ($company?->time_format ?? 'H:i')),
                'duration_months' => $this->start_date && $this->end_date ?
                    $this->start_date->diffInMonths($this->end_date) + 1 : null,
            ],
            'financial' => [
                'currency' => $contractCurrency,
                'currency_symbol' => $currencySymbol,
                'exchange_rate' => $this->exchange_rate ?? 1.0,
                'total_amount' => $this->total_amount,
                'monthly_amount' => $this->monthly_amount,
                'formatted_total' => $this->total_amount ? CurrencyHelper::format((float) $this->total_amount, $contractCurrency) : null,
                'formatted_monthly' => $this->monthly_amount ? CurrencyHelper::format((float) $this->monthly_amount, $contractCurrency) : null,
                'company_currency' => $companyCurrency,
                'currency_converted' => $contractCurrency !== $companyCurrency,
            ],
            'terms' => [
                'payment_terms' => $this->payment_terms,
                'payment_terms_days' => $company?->payment_terms_days ?? 30,
                'late_fee_percentage' => $company?->late_fee_percentage ?? 0,
                'tax_rate' => $company?->default_tax_rate ?? 0,
            ],
            'billboards' => $this->when(
                $this->relationLoaded('billboards'),
                function () use ($contractCurrency) {
                    return $this->billboards->map(function ($billboard) use ($contractCurrency) {
                        return [
                            'id' => $billboard->id,
                            'uuid' => $billboard->uuid,
                            'code' => $billboard->code,
                            'name' => $billboard->name,
                            'location' => $billboard->location,
                            'rate' => $billboard->pivot?->rate ?? $billboard->monthly_rate,
                            'formatted_rate' => CurrencyHelper::format((float) ($billboard->pivot?->rate ?? $billboard->monthly_rate), $contractCurrency),
                            'notes' => $billboard->pivot?->notes,
                            'dimensions' => $billboard->width && $billboard->height ?
                                $billboard->width . ' x ' . $billboard->height : null,
                        ];
                    });
                }
            ),
            'template' => $this->when(
                $this->relationLoaded('template') && $this->template,
                function () {
                    return [
                        'id' => $this->template->id,
                        'uuid' => $this->template->uuid,
                        'name' => $this->template->name,
                    ];
                }
            ),
            'created_by' => $this->when(
                $this->relationLoaded('createdBy') && $this->createdBy,
                function () {
                    return [
                        'id' => $this->createdBy->id,
                        'name' => $this->createdBy->name,
                        'email' => $this->createdBy->email,
                    ];
                }
            ),
            'company' => $this->when(
                $company,
                function () use ($company, $companyCurrency, $timezone, $dateFormat) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'currency' => $companyCurrency,
                        'timezone' => $timezone,
                        'date_format' => $dateFormat,
                    ];
                }
            ),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->setTimezone($timezone)->format($dateFormat),
            'updated_at' => $this->updated_at?->setTimezone($timezone)->format($dateFormat),
            'actions' => [
                'can_view' => true,
                'can_edit' => in_array($this->status, ['draft', 'pending', 'pending_approval']),
                'can_delete' => $this->status === 'draft',
                'can_approve' => in_array($this->status, ['pending', 'pending_approval']),
                'can_sign' => $this->status === 'approved',
                'can_cancel' => in_array($this->status, ['approved', 'active']),
            ],
        ];
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending', 'pending_approval' => 'yellow',
            'approved' => 'blue',
            'active' => 'green',
            'completed' => 'purple',
            'cancelled' => 'red',
            'expired' => 'orange',
            default => 'gray',
        };
    }
}
