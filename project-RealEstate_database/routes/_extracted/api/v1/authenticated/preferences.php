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

Route::get('recommendations/estates', [RecommendationController::class, 'estates'])
    ->name('recommendations.estates');
