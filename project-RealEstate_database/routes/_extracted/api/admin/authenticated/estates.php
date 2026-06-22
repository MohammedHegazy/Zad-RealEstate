<?php

use App\Http\Controllers\Api\Admin\EstateAdController;
use App\Http\Controllers\Api\Admin\EstateController;
use App\Http\Controllers\Api\Admin\EstateImageController;
use App\Http\Controllers\Api\Admin\EstateVideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Estates — moderation & media management
|--------------------------------------------------------------------------
*/

Route::prefix('estates')->name('estates.')->group(function () {
    Route::get('/', [EstateController::class, 'index'])->name('index');
    Route::get('{estate}', [EstateController::class, 'show'])->name('show');
    Route::patch('{estate}/status', [EstateController::class, 'updateStatus'])->name('status');
    Route::delete('{estate}', [EstateController::class, 'destroy'])->name('destroy');

    Route::prefix('{estate}')->group(function () {
        Route::prefix('images')->name('images.')->group(function () {
            Route::get('/', [EstateImageController::class, 'index'])->name('index');
            Route::post('/', [EstateImageController::class, 'store'])->name('store');
            Route::patch('{image}/primary', [EstateImageController::class, 'setPrimary'])->name('primary');
            Route::delete('{image}', [EstateImageController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('videos')->name('videos.')->group(function () {
            Route::get('/', [EstateVideoController::class, 'index'])->name('index');
            Route::post('/', [EstateVideoController::class, 'store'])->name('store');
            Route::delete('{video}', [EstateVideoController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('ads')->name('ads.')->group(function () {
            Route::get('/', [EstateAdController::class, 'index'])->name('index');
            Route::post('/', [EstateAdController::class, 'store'])->name('store');
            Route::patch('{ad}/main', [EstateAdController::class, 'setMain'])->name('main');
            Route::delete('{ad}', [EstateAdController::class, 'destroy'])->name('destroy');
        });
    });
});
