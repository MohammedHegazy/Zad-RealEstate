<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

Route::prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('unread-count', [MessageController::class, 'unreadCount'])->name('unread-count');
    Route::get('conversation/{user}', [MessageController::class, 'conversation'])->name('conversation');
    Route::post('/', [MessageController::class, 'store'])->name('store');
    Route::get('{message}', [MessageController::class, 'show'])->name('show');
    Route::patch('{message}/read', [MessageController::class, 'markAsRead'])->name('read');
    Route::delete('{message}', [MessageController::class, 'destroy'])->name('destroy');
});
