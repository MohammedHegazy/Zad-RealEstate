<?php

use App\Http\Controllers\Api\MarketAnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Market analytics — listing and prediction trends
|--------------------------------------------------------------------------
*/

Route::prefix('market-analytics')->name('market-analytics.')->group(function () {
    Route::get('trends', [MarketAnalyticsController::class, 'marketTrends'])->name('trends');
});
