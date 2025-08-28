<?php

namespace App\Services;

use App\Events\SystemNotificationCreated;
use App\Models\Company;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;

class SystemNotificationService
{
    /**
     * Create a company-wide notification
     */
    public function createCompanyNotification(
        Company $company,
        string $type,
        string $title,
        string $message,
        string $level = 'info',
        array $data = [],
        ?Carbon $expiresAt = null
    ): SystemNotification {
        $notification = SystemNotification::create([
            'type' => $type,
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'company_id' => $company->id,
            'expires_at' => $expiresAt,
        ]);

        event(new SystemNotificationCreated($notification));

        return $notification;
    }

    /**
     * Create a user-specific notification
     */
    public function createUserNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        string $level = 'info',
        array $data = [],
        ?Carbon $expiresAt = null
    ): SystemNotification {
        $notification = SystemNotification::create([
            'type' => $type,
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'user_id' => $user->id,
            'expires_at' => $expiresAt,
        ]);

        event(new SystemNotificationCreated($notification));

        return $notification;
    }

    /**
     * Create subscription limit notifications
     */
    public function notifySubscriptionLimit(
        Company $company,
        string $resource,
        int $current,
        int $limit,
        string $planId
    ): SystemNotification {
        $percentage = $limit > 0 ? ($current / $limit) * 100 : 100;
        
        $level = match(true) {
            $percentage >= 100 => 'error',
            $percentage >= 90 => 'warning',
            $percentage >= 75 => 'info',
            default => 'success'
        };

        $title = match(true) {
            $percentage >= 100 => ucfirst($resource) . ' Limit Reached',
            $percentage >= 90 => ucfirst($resource) . ' Limit Almost Reached',
            default => ucfirst($resource) . ' Usage Update'
        };

        $message = match(true) {
            $percentage >= 100 => "You've reached your {$resource} limit ({$current}/{$limit}) for the {$planId} plan. Upgrade to continue adding {$resource}.",
            $percentage >= 90 => "You're almost at your {$resource} limit ({$current}/{$limit}) for the {$planId} plan. Consider upgrading soon.",
            default => "Current {$resource} usage: {$current}/{$limit} for the {$planId} plan."
        };

        return $this->createCompanyNotification(
            $company,
            'subscription_limit',
            $title,
            $message,
            $level,
            [
                'resource' => $resource,
                'current' => $current,
                'limit' => $limit,
                'plan' => $planId,
                'percentage' => round($percentage, 1),
            ]
        );
    }

    /**
     * Get active notifications for a company
     */
    public function getCompanyNotifications(Company $company, bool $unreadOnly = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = SystemNotification::forCompany($company)
            ->active()
            ->notDismissed()
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->get();
    }

    /**
     * Check and create limit notifications for a company
     */
    public function checkAndNotifyLimits(Company $company): array
    {
        $notifications = [];
        $subscriptionLimitService = app(SubscriptionLimitService::class);
        $usage = $subscriptionLimitService->getUsageSummary($company);

        foreach (['billboards', 'contracts', 'team_members', 'templates'] as $resource) {
            $resourceData = $usage[$resource];
            $current = $resourceData['current'];
            $limit = $resourceData['limit'];

            // Only notify if there's a limit (not unlimited)
            if ($limit !== null && $current >= $limit * 0.75) { // 75% threshold
                // Check if we already have a recent notification for this
                $existingNotification = SystemNotification::forCompany($company)
                    ->ofType('subscription_limit')
                    ->where('data->resource', $resource)
                    ->where('created_at', '>', now()->subDays(1))
                    ->first();

                if (!$existingNotification) {
                    $notifications[] = $this->notifySubscriptionLimit(
                        $company,
                        $resource,
                        $current,
                        $limit,
                        $usage['plan']
                    );
                }
            }
        }

        return $notifications;
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications(): int
    {
        return SystemNotification::where('expires_at', '<', now())
            ->delete();
    }
}
