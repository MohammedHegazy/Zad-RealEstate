<?php

use App\Http\Controllers\Api\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/', [NotificationController::class, 'store'])->name('store');
    Route::get('{notification}', [NotificationController::class, 'show'])->name('show');
    Route::patch('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});
