<?php

use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Smart recommendations — preferences & suggested estates
|--------------------------------------------------------------------------
*/

Route::prefix('my/preferences')->name('my.preferences.')->group(function () {
    Route::get('/', [UserPreferenceController::class, 'show'])->name('show');
    Route::post('/', [UserPreferenceController::class, 'store'])->name('store');
    Route::put('/', [UserPreferenceController::class, 'store'])->name('update');
    Route::delete('/', [UserPreferenceController::class, 'destroy'])->name('destroy');
});

Route::prefix('recommendations')->name('recommendations.')->group(function () {
    Route::get('/', [RecommendationController::class, 'index'])->name('index');
    Route::get('top', [RecommendationController::class, 'top'])->name('top');
    Route::get('similar-estates/{estate}', [RecommendationController::class, 'similarEstates'])->name('similar-estates');
    Route::post('refresh', [RecommendationController::class, 'refresh'])->name('refresh');
    Route::get('{recommendation}', [RecommendationController::class, 'show'])->name('show');
});
