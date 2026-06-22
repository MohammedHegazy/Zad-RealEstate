<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanySocialMediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Companies — owner CRUD, agents & social links
|--------------------------------------------------------------------------
*/

Route::get('my/companies', [CompanyController::class, 'myCompanies'])->name('my.companies');
Route::post('my/companies', [CompanyController::class, 'store'])->name('my.companies.store');
Route::put('my/companies/{company}', [CompanyController::class, 'update'])->name('my.companies.update');
Route::delete('my/companies/{company}', [CompanyController::class, 'destroy'])->name('my.companies.destroy');

Route::prefix('my/companies/{company}')->name('my.companies.')->group(function () {
    Route::prefix('agents')->name('agents.')->group(function () {
        Route::get('/', [AgentController::class, 'indexForCompany'])->name('index');
        Route::post('/', [AgentController::class, 'storeForCompany'])->name('store');
        Route::put('{agent}', [AgentController::class, 'updateForCompany'])->name('update');
        Route::delete('{agent}', [AgentController::class, 'destroyForCompany'])->name('destroy');
    });

    Route::prefix('social-links')->name('social-links.')->group(function () {
        Route::get('/', [CompanySocialMediaController::class, 'index'])->name('index');
        Route::post('/', [CompanySocialMediaController::class, 'store'])->name('store');
        Route::put('{socialLink}', [CompanySocialMediaController::class, 'update'])->name('update');
        Route::delete('{socialLink}', [CompanySocialMediaController::class, 'destroy'])->name('destroy');
    });
});
