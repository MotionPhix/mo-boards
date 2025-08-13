<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CompanySettingsService;
use Illuminate\Console\Command;

class InitializeCompanySettingsCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'company:initialize-settings {--force : Force initialization even if settings exist}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Initialize company settings with defaults and update existing data to use centralized system';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $this->info('Initializing company settings...');

    $companies = Company::all();
    $settingsService = app(CompanySettingsService::class);

    $progressBar = $this->output->createProgressBar($companies->count());
    $progressBar->start();

    foreach ($companies as $company) {
      $this->initializeCompanySettings($company, $settingsService);
      $progressBar->advance();
    }

    $progressBar->finish();
    $this->newLine();

    $this->info("Successfully initialized settings for {$companies->count()} companies.");

    return self::SUCCESS;
  }

  private function initializeCompanySettings(Company $company, CompanySettingsService $settingsService): void
  {
    $updates = [];

    // Initialize contract number settings
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
      // Set current to the highest existing contract number for this company
      $lastContract = $company->contracts()
        ->whereNotNull('contract_number')
        ->orderBy('created_at', 'desc')
        ->first();

      $updates['contract_number_current'] = $lastContract ?
        $this->extractNumberFromContractNumber($lastContract->contract_number) : 0;
    }

    // Initialize billboard code settings
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
      // Set current to the highest existing billboard code for this company
      $lastBillboard = $company->billboards()
        ->whereNotNull('code')
        ->orderBy('created_at', 'desc')
        ->first();

      $updates['billboard_code_current'] = $lastBillboard ?
        $this->extractNumberFromBillboardCode($lastBillboard->code) : 0;
    }

    // Initialize business settings
    if (is_null($company->currency)) {
      $updates['currency'] = 'USD';
    }
    if (is_null($company->timezone)) {
      $updates['timezone'] = 'UTC';
    }
    if (is_null($company->date_format)) {
      $updates['date_format'] = 'Y-m-d';
    }
    if (is_null($company->time_format)) {
      $updates['time_format'] = 'H:i';
    }
    if (is_null($company->payment_terms_days)) {
      $updates['payment_terms_days'] = 30;
    }

    // Initialize notification settings
    if (is_null($company->notification_settings)) {
      $updates['notification_settings'] = [
        'email_contracts' => true,
        'email_payments' => true,
        'email_billboards' => true,
        'email_team' => true,
        'slack_webhook' => null,
      ];
    }

    if (!empty($updates)) {
      $company->update($updates);
      $this->line("  Initialized settings for: {$company->name}");
    }

    // Update existing contracts to use company currency if not set
    $this->updateExistingContracts($company);
  }

  private function updateExistingContracts(Company $company): void
  {
    $contractsToUpdate = $company->contracts()
      ->whereNull('currency')
      ->get();

    foreach ($contractsToUpdate as $contract) {
      $contract->update([
        'currency' => $company->currency ?? 'USD',
        'exchange_rate' => 1.000000,
      ]);
    }

    if ($contractsToUpdate->count() > 0) {
      $this->line("    Updated {$contractsToUpdate->count()} contracts with currency settings");
    }
  }

  private function extractNumberFromContractNumber(string $contractNumber): int
  {
    // Extract numbers from contract number (e.g., "CT-2025-0001" -> 1)
    preg_match('/(\d+)/', $contractNumber, $matches);
    return isset($matches[1]) ? (int)$matches[1] : 0;
  }

  private function extractNumberFromBillboardCode(string $billboardCode): int
  {
    // Extract numbers from billboard code (e.g., "BB001" -> 1)
    preg_match('/(\d+)/', $billboardCode, $matches);
    return isset($matches[1]) ? (int)$matches[1] : 0;
  }
}
