<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

Route::get('my/notifications', [NotificationController::class, 'index'])->name('my.notifications');

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::patch('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('{notification}', [NotificationController::class, 'show'])->name('show');
    Route::patch('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});
