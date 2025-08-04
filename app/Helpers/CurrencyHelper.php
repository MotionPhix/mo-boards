<?php

namespace App\Helpers;

class CurrencyHelper
{
  /**
   * Get currency symbol based on currency code
   */
  public static function getSymbol(string $currency): string
  {
    return match ($currency) {
      // Major World Currencies
      'USD' => '$',
      'EUR' => '€',
      'GBP' => '£',
      'CAD' => 'CA$',
      'AUD' => 'A$',
      'JPY' => '¥',
      'CNY' => '¥',
      'INR' => '₹',

      // African Currencies
      'MWK' => 'MK',     // Malawi Kwacha
      'ZMW' => 'ZK',     // Zambian Kwacha
      'ZAR' => 'R',      // South African Rand
      'ZWL' => 'Z$',     // Zimbabwean Dollar
      'MZN' => 'MT',     // Mozambican Metical
      'TZS' => 'TSh',    // Tanzanian Shilling

      default => $currency . ' ',
    };
  }

  /**
   * Get formatted currency amount
   */
  public static function format(float $amount, string $currency, int $decimals = 2): string
  {
    $symbol = self::getSymbol($currency);
    return $symbol . number_format($amount, $decimals);
  }

  /**
   * Get all available currencies with their symbols and names
   */
  public static function getAvailableCurrencies(): array
  {
    return [
      // Major World Currencies
      'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
      'EUR' => ['symbol' => '€', 'name' => 'Euro'],
      'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
      'CAD' => ['symbol' => 'CA$', 'name' => 'Canadian Dollar'],
      'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
      'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen'],
      'CNY' => ['symbol' => '¥', 'name' => 'Chinese Yuan'],
      'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],

      // African Currencies
      'MWK' => ['symbol' => 'MK', 'name' => 'Malawi Kwacha'],
      'ZMW' => ['symbol' => 'ZK', 'name' => 'Zambian Kwacha'],
      'ZAR' => ['symbol' => 'R', 'name' => 'South African Rand'],
      'ZWL' => ['symbol' => 'Z$', 'name' => 'Zimbabwean Dollar'],
      'MZN' => ['symbol' => 'MT', 'name' => 'Mozambican Metical'],
      'TZS' => ['symbol' => 'TSh', 'name' => 'Tanzanian Shilling'],
    ];
  }

  /**
   * Get currencies formatted for dropdown options
   */
  public static function getDropdownOptions(): array
  {
    $currencies = self::getAvailableCurrencies();

    return collect($currencies)->map(function ($details, $code) {
      return [
        'value' => $code,
        'label' => "{$details['name']} ({$details['symbol']})"
      ];
    })->values()->toArray();
  }
}
