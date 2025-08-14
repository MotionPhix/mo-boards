<?php

namespace App\Observers;

use App\Models\Billboard;
use App\Services\SystemNotificationService;

class BillboardObserver
{
    public function __construct(
        private readonly SystemNotificationService $notificationService
    ) {
        // Dependencies injected via constructor.
    }

    /**
     * Handle the Billboard "created" event.
     */
    public function created(Billboard $billboard): void
    {
        // Check if the company is approaching or has reached limits
        $company = $billboard->company;
        
        if ($company) {
            $this->notificationService->checkAndNotifyLimits($company);
        }
    }

    /**
     * Handle the Billboard "updated" event.
     */
    public function updated(Billboard $billboard): void
    {
        //
    }

    /**
     * Handle the Billboard "deleted" event.
     */
    public function deleted(Billboard $billboard): void
    {
        // When a billboard is deleted, we might want to notify about available quota
        // For now, we'll just check limits again in case they're back under the threshold
        $company = $billboard->company;
        
        if ($company) {
            $this->notificationService->checkAndNotifyLimits($company);
        }
    }

    /**
     * Handle the Billboard "restored" event.
     */
    public function restored(Billboard $billboard): void
    {
        $company = $billboard->company;
        
        if ($company) {
            $this->notificationService->checkAndNotifyLimits($company);
        }
    }

    /**
     * Handle the Billboard "force deleted" event.
     */
    public function forceDeleted(Billboard $billboard): void
    {
        //
    }
}
