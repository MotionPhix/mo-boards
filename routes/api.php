<?php

use App\Http\Controllers\SystemNotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Notification routes - using standard auth middleware for web session authentication
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [SystemNotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [SystemNotificationController::class, 'markAsRead']);
    Route::post('/notifications/{notification}/dismiss', [SystemNotificationController::class, 'dismiss']);
    Route::post('/notifications/read-all', [SystemNotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [SystemNotificationController::class, 'unreadCount']);
});
