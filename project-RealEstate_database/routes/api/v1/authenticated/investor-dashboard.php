<?php

use App\Http\Controllers\Api\Investor\InvestorDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('investor/dashboard')->name('investor.dashboard.')->group(function () {
    Route::get('/', [InvestorDashboardController::class, 'summary'])->name('summary');
});

Route::get('my/investor-dashboard', [InvestorDashboardController::class, 'summary'])
    ->name('my.investor-dashboard');
