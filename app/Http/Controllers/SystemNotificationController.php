<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemNotificationController extends Controller
{
    public function __construct(
        private readonly SystemNotificationService $notificationService
    ) {
        // Dependencies injected via constructor.
    }

    /**
     * Get notifications for the current user/company
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        if (!$company) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }

        // Get company notifications
        $companyNotifications = $this->notificationService->getCompanyNotifications($company);
        
        // Get user-specific notifications  
        $userNotifications = $this->notificationService->getUserNotifications($user);

        // Combine and sort by created_at desc
        $allNotifications = $companyNotifications->merge($userNotifications)
            ->sortByDesc('created_at')
            ->values();

        $unreadCount = $allNotifications->where('is_read', false)->count();

        return response()->json([
            'notifications' => $allNotifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'level' => $notification->level,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'is_dismissed' => $notification->is_dismissed,
                    'created_at' => $notification->created_at->toISOString(),
                    'read_at' => $notification->read_at?->toISOString(),
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(SystemNotification $notification): JsonResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        // Ensure user can access this notification
        if ($notification->company_id && $notification->company_id !== $company?->id) {
            abort(403, 'Unauthorized');
        }

        if ($notification->user_id && $notification->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => [
                'id' => $notification->id,
                'is_read' => true,
                'read_at' => $notification->read_at->toISOString(),
            ]
        ]);
    }

    /**
     * Dismiss a notification
     */
    public function dismiss(SystemNotification $notification): JsonResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        // Ensure user can access this notification
        if ($notification->company_id && $notification->company_id !== $company?->id) {
            abort(403, 'Unauthorized');
        }

        if ($notification->user_id && $notification->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $notification->dismiss();

        return response()->json([
            'message' => 'Notification dismissed',
            'notification' => [
                'id' => $notification->id,
                'is_dismissed' => true,
                'dismissed_at' => $notification->dismissed_at->toISOString(),
            ]
        ]);
    }

    /**
     * Mark all notifications as read for the current user/company
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        $companyCount = 0;
        $userCount = 0;

        if ($company) {
            $companyCount = $this->notificationService->markAllAsReadForCompany($company);
        }

        $userCount = $this->notificationService->markAllAsReadForUser($user);

        return response()->json([
            'message' => 'All notifications marked as read',
            'marked_count' => $companyCount + $userCount,
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        $count = 0;

        if ($company) {
            $count += SystemNotification::forCompany($company)
                ->active()
                ->notDismissed()
                ->unread()
                ->count();
        }

        $count += SystemNotification::forUser($user)
            ->active()
            ->notDismissed()
            ->unread()
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
