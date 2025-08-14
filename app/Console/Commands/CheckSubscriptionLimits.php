<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\SystemNotificationService;
use Illuminate\Console\Command;

class CheckSubscriptionLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-limits {--company-id= : Check specific company ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscription limits and create notifications for companies approaching or exceeding limits';

    public function __construct(
        private readonly SystemNotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Checking subscription limits...');

        $companyId = $this->option('company-id');
        
        if ($companyId) {
            $company = Company::find($companyId);
            if (!$company) {
                $this->error("Company with ID {$companyId} not found.");
                return;
            }
            $companies = collect([$company]);
            $this->info("Checking limits for company: {$company->name}");
        } else {
            $companies = Company::where('is_active', true)->get();
            $this->info("Checking limits for {$companies->count()} active companies");
        }

        $totalNotifications = 0;

        foreach ($companies as $company) {
            $this->line("Checking company: {$company->name} (Plan: {$company->subscription_plan})");
            
            $notifications = $this->notificationService->checkAndNotifyLimits($company);
            
            if (count($notifications) > 0) {
                $this->warn("  Created " . count($notifications) . " notifications:");
                foreach ($notifications as $notification) {
                    $this->line("    - {$notification->level}: {$notification->title}");
                }
            } else {
                $this->line("  No new notifications needed");
            }
            
            $totalNotifications += count($notifications);
        }

        $this->info("Completed! Created {$totalNotifications} total notifications.");
        
        // Clean up expired notifications
        $expired = $this->notificationService->cleanupExpiredNotifications();
        if ($expired > 0) {
            $this->info("Cleaned up {$expired} expired notifications.");
        }
    }
}
