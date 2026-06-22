<?php

use App\Http\Controllers\Api\Admin\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('conversation', [MessageController::class, 'conversation'])->name('conversation');
    Route::get('{message}', [MessageController::class, 'show'])->name('show');
    Route::delete('{message}', [MessageController::class, 'destroy'])->name('destroy');
});
