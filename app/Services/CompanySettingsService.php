<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Billboard;
use App\Models\Contract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CompanySettingsService
{
  /**
   * Generate the next contract number for a company
   */
  public function generateContractNumber(Company $company): string
  {
    $prefix = $company->contract_number_prefix ?? '';
    $suffix = $company->contract_number_suffix ?? '';
    $format = $company->contract_number_format ?? 'sequential';
    $length = $company->contract_number_length ?? 6;

    // Get the next number and increment the counter
    $currentNumber = $company->contract_number_current ?? 0;
    $nextNumber = $currentNumber + 1;

    // Update the current number in database
    $company->update(['contract_number_current' => $nextNumber]);

    $formattedNumber = $this->formatNumber($nextNumber, $format, $length);

    return "{$prefix}{$formattedNumber}{$suffix}";
  }

  /**
   * Generate the next billboard code for a company
   */
  public function generateBillboardCode(Company $company): string
  {
    $prefix = $company->billboard_code_prefix ?? '';
    $suffix = $company->billboard_code_suffix ?? '';
    $format = $company->billboard_code_format ?? 'sequential';
    $length = $company->billboard_code_length ?? 4;

    // Get the next number and increment the counter
    $currentNumber = $company->billboard_code_current ?? 0;
    $nextNumber = $currentNumber + 1;

    // Update the current number in database
    $company->update(['billboard_code_current' => $nextNumber]);

    $formattedCode = $this->formatNumber($nextNumber, $format, $length);

    return "{$prefix}{$formattedCode}{$suffix}";
  }

  /**
   * Format a number based on the specified format
   */
  private function formatNumber(int $number, string $format, int $length): string
  {
    return match ($format) {
      'sequential' => str_pad($number, $length, '0', STR_PAD_LEFT),
      'date_based' => $this->formatDateBasedNumber($number, $length),
      'location_based' => str_pad($number, $length, '0', STR_PAD_LEFT),
      'custom' => str_pad($number, $length, '0', STR_PAD_LEFT),
      default => str_pad($number, $length, '0', STR_PAD_LEFT),
    };
  }

  /**
   * Format a date-based number (YYYYMM + sequential)
   */
  private function formatDateBasedNumber(int $number, int $length): string
  {
    $now = now();
    $yearMonth = $now->format('Ym');
    $remainingLength = max(1, $length - 6); // Reserve 6 digits for YYYYMM
    $paddedNumber = str_pad($number, $remainingLength, '0', STR_PAD_LEFT);

    return $yearMonth . $paddedNumber;
  }

  /**
   * Get exchange rate between two currencies
   */
  public function getExchangeRate(string $fromCurrency, string $toCurrency): float
  {
    if ($fromCurrency === $toCurrency) {
      return 1.0;
    }

    $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";

    return Cache::remember($cacheKey, 3600, function () use ($fromCurrency, $toCurrency) {
      return $this->fetchExchangeRate($fromCurrency, $toCurrency);
    });
  }

  /**
   * Fetch exchange rate from external API
   */
  private function fetchExchangeRate(string $fromCurrency, string $toCurrency): float
  {
    try {
      // Using a free exchange rate API (you can replace with your preferred service)
      $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/{$fromCurrency}");

      if ($response->successful()) {
        $data = $response->json();
        return $data['rates'][$toCurrency] ?? 1.0;
      }
    } catch (\Exception $e) {
      \Log::warning("Failed to fetch exchange rate from {$fromCurrency} to {$toCurrency}: " . $e->getMessage());
    }

    // Fallback to 1.0 if API fails
    return 1.0;
  }

  /**
   * Convert amount from one currency to another
   */
  public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
  {
    if ($fromCurrency === $toCurrency) {
      return $amount;
    }

    $exchangeRate = $this->getExchangeRate($fromCurrency, $toCurrency);
    return round($amount * $exchangeRate, 2);
  }

  /**
   * Get all billboard prices in a specific currency
   */
  public function getBillboardPricesInCurrency(Company $company, string $currency): \Illuminate\Database\Eloquent\Collection
  {
    $billboards = $company->billboards()->get();
    $companyCurrency = $company->currency ?? 'USD';

    return $billboards->map(function ($billboard) use ($currency, $companyCurrency) {
      $convertedRate = $this->convertCurrency(
        $billboard->rate,
        $companyCurrency,
        $currency
      );

      $billboard->converted_rate = $convertedRate;
      $billboard->original_currency = $companyCurrency;
      $billboard->converted_currency = $currency;

      return $billboard;
    });
  }

  /**
   * Calculate contract total in specified currency
   */
  public function calculateContractTotal(Contract $contract): array
  {
    $company = $contract->company;
    $companyCurrency = $company->currency ?? 'USD';
    $contractCurrency = $contract->currency ?? $companyCurrency;

    $total = 0;
    $billboardDetails = [];

    foreach ($contract->billboards as $billboard) {
      $pivotData = $billboard->pivot;
      $originalRate = $pivotData->rate ?? $billboard->rate;

      // Convert rate to contract currency if needed
      $convertedRate = $this->convertCurrency(
        $originalRate,
        $companyCurrency,
        $contractCurrency
      );

      $lineTotal = $convertedRate * ($pivotData->duration ?? 1);
      $total += $lineTotal;

      $billboardDetails[] = [
        'billboard_id' => $billboard->id,
        'billboard_name' => $billboard->name,
        'original_rate' => $originalRate,
        'converted_rate' => $convertedRate,
        'duration' => $pivotData->duration ?? 1,
        'line_total' => $lineTotal,
        'original_currency' => $companyCurrency,
        'contract_currency' => $contractCurrency,
      ];
    }

    return [
      'total' => round($total, 2),
      'currency' => $contractCurrency,
      'billboards' => $billboardDetails,
      'exchange_rate' => $this->getExchangeRate($companyCurrency, $contractCurrency),
    ];
  }

  /**
   * Initialize company settings with defaults if not set
   */
  public function initializeCompanySettings(Company $company): void
  {
    $updates = [];

    // Contract number settings
    if (is_null($company->contract_number_format)) {
      $updates['contract_number_format'] = 'sequential';
    }
    if (is_null($company->contract_number_length)) {
      $updates['contract_number_length'] = 6;
    }
    if (is_null($company->contract_number_start)) {
      $updates['contract_number_start'] = 1;
    }
    if (is_null($company->contract_number_current)) {
      $updates['contract_number_current'] = 0;
    }

    // Billboard code settings
    if (is_null($company->billboard_code_format)) {
      $updates['billboard_code_format'] = 'sequential';
    }
    if (is_null($company->billboard_code_length)) {
      $updates['billboard_code_length'] = 4;
    }
    if (is_null($company->billboard_code_start)) {
      $updates['billboard_code_start'] = 1;
    }
    if (is_null($company->billboard_code_current)) {
      $updates['billboard_code_current'] = 0;
    }

    // Currency and other settings
    if (is_null($company->currency)) {
      $updates['currency'] = 'USD';
    }
    if (is_null($company->timezone)) {
      $updates['timezone'] = 'UTC';
    }

    if (!empty($updates)) {
      $company->update($updates);
    }
  }
}
