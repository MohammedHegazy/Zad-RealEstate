<?php

use App\Http\Controllers\Api\InvestmentAnalysisController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Investment analyses (saved ROI scenarios)
|--------------------------------------------------------------------------
*/

Route::prefix('my/investment-analyses')->name('my.investment-analyses.')->group(function () {
    Route::get('/', [InvestmentAnalysisController::class, 'index'])->name('index');
    Route::post('/', [InvestmentAnalysisController::class, 'store'])->name('store');
    Route::get('{investment_analysis}', [InvestmentAnalysisController::class, 'show'])->name('show');
    Route::put('{investment_analysis}', [InvestmentAnalysisController::class, 'update'])->name('update');
    Route::delete('{investment_analysis}', [InvestmentAnalysisController::class, 'destroy'])->name('destroy');
});
