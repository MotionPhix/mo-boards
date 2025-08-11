<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Company;
use App\Enums\ContractStatus;
use App\Helpers\CurrencyHelper;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PlaceholderService
{
    /**
     * Replace placeholders in content with actual values
     */
    public function replacePlaceholders(string $content, Contract $contract): string
    {
        $placeholders = $this->getPlaceholderValues($contract);

        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        // Render billboard layouts if placeholders present
        if (str_contains($content, '{{billboards_table}}')) {
            $content = str_replace('{{billboards_table}}', $this->renderBillboardsTable($contract), $content);
        }

        if (str_contains($content, '{{billboards_list}}')) {
            $content = str_replace('{{billboards_list}}', $this->renderBillboardsList($contract), $content);
        }

        return $content;
    }

    /**
     * Get all placeholder values for a contract
     */
    public function getPlaceholderValues(Contract $contract): array
    {
        $company = $contract->company;

    $values = [
            // Company Information
            '{{company_name}}' => $company->name ?? '',
            '{{company_logo}}' => $this->getCompanyLogo($company),
            '{{company_logo_small}}' => $this->getCompanyLogo($company, 'small'),
            '{{company_address}}' => $this->getCompanyFullAddress($company),
            '{{company_city}}' => $company->city ?? '',
            '{{company_state}}' => $company->state ?? '',
            '{{company_zip_code}}' => $company->zip_code ?? '',
            '{{company_country}}' => $company->country ?? '',
            '{{company_phone}}' => $company->phone ?? '',
            '{{company_email}}' => $company->email ?? '',
            '{{company_website}}' => $company->website ?? '',
            '{{company_tax_id}}' => $company->tax_id ?? '',
            '{{company_description}}' => $company->company_description ?? '',

            // Client Information
            '{{client_name}}' => $contract->client_name ?? '',
            '{{client_company}}' => $contract->client_company ?? '',
            '{{client_address}}' => $contract->client_address ?? '',
            '{{client_email}}' => $contract->client_email ?? '',
            '{{client_phone}}' => $contract->client_phone ?? '',

            // Contract Details
            '{{contract_number}}' => $contract->contract_number ?? '',
            '{{contract_type}}' => $this->getContractType($contract),
            '{{start_date}}' => $contract->start_date ? $contract->start_date->format('F j, Y') : '',
            '{{end_date}}' => $contract->end_date ? $contract->end_date->format('F j, Y') : '',
            '{{status}}' => $this->formatStatus($contract->status),
            '{{signed_at}}' => $contract->signed_at ? $contract->signed_at->format('F j, Y') : '',
            '{{signed_by}}' => $contract->signed_by ?? '',

            // Financial Information
            '{{total_amount}}' => $this->formatCurrency($contract->total_amount, $contract->currency),
            '{{monthly_amount}}' => $this->formatCurrency($contract->monthly_amount, $contract->currency),
            '{{payment_terms}}' => $this->formatPaymentTerms($contract->payment_terms),
            '{{currency}}' => $contract->currency ?? 'USD',
            '{{exchange_rate}}' => $contract->exchange_rate ?? '1.000000',

            // Billboard Information
            '{{billboard_locations}}' => $this->getBillboardLocations($contract),

            // Dates & System
            '{{today_date}}' => Carbon::now()->format('F j, Y'),
            '{{current_year}}' => Carbon::now()->format('Y'),
            '{{current_month}}' => Carbon::now()->format('F'),
    ];

    // Special layout placeholders (rendered HTML blocks)
    $values['{{billboards_table}}'] = $this->renderBillboardsTable($contract);
    $values['{{billboards_list}}'] = $this->renderBillboardsList($contract);

    return $values;
    }

    /**
     * Get company logo HTML
     */
    private function getCompanyLogo(Company $company, string $size = 'default'): string
    {
        $logoMedia = $company->getFirstMedia('logo');

        if ($logoMedia) {
            $url = $logoMedia->getUrl();
            $alt = $company->name . ' Logo';

            $dimensions = match ($size) {
                'small' => 'width="150" height="auto"',
                default => 'width="250" height="auto"'
            };

            return "<img src=\"{$url}\" alt=\"{$alt}\" {$dimensions} style=\"max-width: 100%; height: auto;\" />";
        }

        // Fallback to Gravatar or placeholder
        $email = $company->email ?? 'default@example.com';
        $hash = md5(strtolower(trim($email)));
        $gravatarUrl = "https://www.gravatar.com/avatar/{$hash}?s=200&d=identicon";

        $dimensions = match ($size) {
            'small' => 'width="150" height="150"',
            default => 'width="200" height="200"'
        };

        return "<img src=\"{$gravatarUrl}\" alt=\"{$company->name} Logo\" {$dimensions} style=\"max-width: 100%; height: auto; border-radius: 8px;\" />";
    }

    /**
     * Get formatted company address
     */
    private function getCompanyFullAddress(Company $company): string
    {
        $addressParts = array_filter([
            $company->address,
            $company->city,
            $company->state,
            $company->zip_code,
            $company->country,
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Get contract type based on billboards or other factors
     */
    private function getContractType(Contract $contract): string
    {
        // You can customize this logic based on your business rules
        if ($contract->billboards()->count() > 0) {
            return 'Billboard Advertising Contract';
        }

        return 'Service Contract';
    }

    /**
     * Format payment terms for display
     */
    private function formatPaymentTerms(string $paymentTerms): string
    {
        return match ($paymentTerms) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annual' => 'Semi-Annual',
            'annual' => 'Annual',
            'one_time' => 'One-Time Payment',
            default => Str::title(str_replace('_', ' ', $paymentTerms)),
        };
    }

    /**
     * Format status for display
     */
    private function formatStatus(string $status): string
    {
        // Normalize and map via Enum for a single source of truth
        if ($enum = ContractStatus::tryFrom(strtolower($status))) {
            return $enum->label();
        }
        return Str::title($status);
    }

    /**
     * Format currency amount
     */
    private function formatCurrency(?float $amount, ?string $currency = 'USD'): string
    {
        if ($amount === null) {
            return '';
        }
        return CurrencyHelper::format((float) $amount, $currency ?? 'USD');
    }

    /**
     * Get billboard locations for the contract
     */
    private function getBillboardLocations(Contract $contract): string
    {
        $billboards = $contract->billboards;

        if ($billboards->isEmpty()) {
            return 'No billboard locations specified';
        }

        $locations = $billboards->map(function ($billboard) {
            $location = $billboard->location ?? 'Unknown Location';
            $code = $billboard->code ? " ({$billboard->code})" : '';
            return $location . $code;
        });

        return $locations->join(', ');
    }

    /**
     * Get available placeholders with descriptions
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            'Company Information' => [
                '{{company_name}}' => 'Company Name',
                '{{company_logo}}' => 'Company Logo (Full Size)',
                '{{company_logo_small}}' => 'Company Logo (Small)',
                '{{company_address}}' => 'Complete Company Address',
                '{{company_city}}' => 'Company City',
                '{{company_state}}' => 'Company State/Province',
                '{{company_zip_code}}' => 'Company ZIP/Postal Code',
                '{{company_country}}' => 'Company Country',
                '{{company_phone}}' => 'Company Phone Number',
                '{{company_email}}' => 'Company Email Address',
                '{{company_website}}' => 'Company Website',
                '{{company_tax_id}}' => 'Company Tax ID',
                '{{company_description}}' => 'Company Description',
            ],
            'Client Information' => [
                '{{client_name}}' => 'Client Full Name',
                '{{client_company}}' => 'Client Company Name',
                '{{client_address}}' => 'Client Address',
                '{{client_email}}' => 'Client Email Address',
                '{{client_phone}}' => 'Client Phone Number',
            ],
            'Contract Details' => [
                '{{contract_number}}' => 'Contract Number',
                '{{contract_type}}' => 'Contract Type',
                '{{start_date}}' => 'Contract Start Date',
                '{{end_date}}' => 'Contract End Date',
                '{{status}}' => 'Contract Status',
                '{{signed_at}}' => 'Date Signed',
                '{{signed_by}}' => 'Signed By',
            ],
            'Financial Information' => [
                '{{total_amount}}' => 'Total Contract Value',
                '{{monthly_amount}}' => 'Monthly Payment Amount',
                '{{payment_terms}}' => 'Payment Terms',
                '{{currency}}' => 'Contract Currency',
                '{{exchange_rate}}' => 'Exchange Rate',
            ],
            'Billboard Information' => [
                '{{billboard_locations}}' => 'Billboard Locations',
                '{{billboards_table}}' => 'Billboards Table (Code, Name, Location, Dimensions, Monthly Rate, Notes)',
                '{{billboards_list}}' => 'Billboards List (Name, Location, Rate)',
            ],
            'Dates & System' => [
                '{{today_date}}' => 'Today\'s Date',
                '{{current_year}}' => 'Current Year',
                '{{current_month}}' => 'Current Month',
            ],
        ];
    }

    /**
     * Render billboards as a styled HTML table similar to the provided design
     */
    private function renderBillboardsTable(Contract $contract): string
    {
        $billboards = $contract->billboards;
        if ($billboards->isEmpty()) {
            return '<div style="margin: 1rem 0; color: #666;">No billboards on this contract.</div>';
        }

        $currency = $contract->currency ?? ($contract->company->currency ?? 'USD');

        $rows = $billboards->map(function ($b) use ($currency) {
            $code = e($b->code ?? '—');
            $name = e($b->name ?? '—');
            $location = e($b->location ?? '—');
            $dimensions = ($b->width && $b->height) ? e(number_format($b->width, 2)) . "' × " . e(number_format($b->height, 2)) . "'" : '—';
            $rate = $b->pivot?->rate ?? $b->monthly_rate ?? 0;
            $formattedRate = e(\App\Helpers\CurrencyHelper::format((float)$rate, $currency));
            $notes = e($b->pivot?->notes ?? '');

            return "<tr>
                <td style='border:1px solid #ddd; padding:12px;'>$code</td>
                <td style='border:1px solid #ddd; padding:12px;'>$name</td>
                <td style='border:1px solid #ddd; padding:12px;'>$location</td>
                <td style='border:1px solid #ddd; padding:12px; text-align:center;'>$dimensions</td>
                <td style='border:1px solid #ddd; padding:12px; text-align:right;'>$formattedRate</td>
                <td style='border:1px solid #ddd; padding:12px;'>$notes</td>
            </tr>";
        })->join('');

        return "<table style='width:100%; border-collapse:collapse; margin: 12px 0; border:1px solid #000;'>
            <thead>
                <tr style='background-color:#f5f5f5;'>
                    <th style='border:1px solid #000; padding:10px; text-align:left;'>Code</th>
                    <th style='border:1px solid #000; padding:10px; text-align:left;'>Name</th>
                    <th style='border:1px solid #000; padding:10px; text-align:left;'>Location</th>
                    <th style='border:1px solid #000; padding:10px; text-align:center;'>Dimensions</th>
                    <th style='border:1px solid #000; padding:10px; text-align:right;'>Monthly Rate</th>
                    <th style='border:1px solid #000; padding:10px; text-align:left;'>Notes</th>
                </tr>
            </thead>
            <tbody>$rows</tbody>
        </table>";
    }

    /**
     * Render billboards as a bullet list
     */
    private function renderBillboardsList(Contract $contract): string
    {
        $billboards = $contract->billboards;
        if ($billboards->isEmpty()) {
            return '<div style="margin: 1rem 0; color: #666;">No billboards on this contract.</div>';
        }
        $currency = $contract->currency ?? ($contract->company->currency ?? 'USD');

        $items = $billboards->map(function ($b) use ($currency) {
            $name = e($b->name ?? '—');
            $location = e($b->location ?? '—');
            $rate = $b->pivot?->rate ?? $b->monthly_rate ?? 0;
            $formattedRate = e(\App\Helpers\CurrencyHelper::format((float)$rate, $currency));
            return "<li><strong>$name</strong> — $location <span style='float:right;'>$formattedRate</span></li>";
        })->join('');

        return "<ul style='margin: 12px 0 12px 1.25rem; padding:0; list-style:disc;'>$items</ul>";
    }
}
