<?php

use App\Http\Controllers\Api\Investor\InvestmentPortfolioController;
use App\Http\Controllers\Api\Investor\MyPortfolioItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Investment portfolios & portfolio properties
|--------------------------------------------------------------------------
*/

Route::prefix('investment-portfolios')->name('investment-portfolios.')->group(function () {
    Route::get('/', [InvestmentPortfolioController::class, 'index'])->name('index');
    Route::post('/', [InvestmentPortfolioController::class, 'store'])->name('store');
    Route::get('{investment_portfolio}', [InvestmentPortfolioController::class, 'show'])->name('show');
    Route::get('{investment_portfolio}/properties', [InvestmentPortfolioController::class, 'properties'])->name('properties');
});

Route::prefix('investment-portfolios/{investment_portfolio}/properties')->name('investment-portfolios.properties.')->group(function () {
    Route::post('/', [MyPortfolioItemController::class, 'storeForPortfolio'])->name('store');
    Route::delete('{property}', [MyPortfolioItemController::class, 'destroyForPortfolio'])->name('destroy');
});

// Legacy routes
Route::prefix('my/portfolios')->name('my.portfolios.')->group(function () {
    Route::get('/', [InvestmentPortfolioController::class, 'index'])->name('index');
    Route::post('/', [InvestmentPortfolioController::class, 'store'])->name('store');
});

Route::prefix('my/portfolio-items')->name('my.portfolio-items.')->group(function () {
    Route::get('/', [MyPortfolioItemController::class, 'index'])->name('index');
    Route::post('/', [MyPortfolioItemController::class, 'store'])->name('store');
    Route::put('{id}', [MyPortfolioItemController::class, 'update'])->name('update');
    Route::delete('{id}', [MyPortfolioItemController::class, 'destroy'])->name('destroy');
});
