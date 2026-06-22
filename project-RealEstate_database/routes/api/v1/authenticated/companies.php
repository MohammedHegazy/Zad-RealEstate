<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanySocialLinkController;
use App\Http\Controllers\Api\EstateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Companies — owner profile (one per user), agents & social links
|--------------------------------------------------------------------------
*/

Route::get('my/company', [CompanyController::class, 'myCompany'])->name('my.company');
Route::post('my/company', [CompanyController::class, 'store'])->name('my.company.store');
Route::match(['put', 'post'], 'my/company', [CompanyController::class, 'update'])->name('my.company.update');
Route::delete('my/company', [CompanyController::class, 'destroy'])->name('my.company.destroy');

Route::prefix('my/company')->name('my.company.')->group(function () {
    Route::prefix('agents')->name('agents.')->group(function () {
        Route::get('/', [AgentController::class, 'indexForCompany'])->name('index');
        Route::post('/', [AgentController::class, 'storeForCompany'])->name('store');
        Route::put('{agent}', [AgentController::class, 'updateForCompany'])->name('update');
        Route::delete('{agent}', [AgentController::class, 'destroyForCompany'])->name('destroy');
        Route::post('{agent}/approve', [AgentController::class, 'approveForCompany'])->name('approve');
        Route::post('{agent}/reject', [AgentController::class, 'rejectForCompany'])->name('reject');
    });

    Route::get('all-estates', [EstateController::class, 'companyAllEstates'])
        ->name('all-estates');

    Route::prefix('social-links')->name('social-links.')->group(function () {
        Route::get('/', [CompanySocialLinkController::class, 'index'])->name('index');
        Route::post('/', [CompanySocialLinkController::class, 'store'])->name('store');
        Route::put('{socialLink}', [CompanySocialLinkController::class, 'update'])->name('update');
        Route::delete('{socialLink}', [CompanySocialLinkController::class, 'destroy'])->name('destroy');
    });
});
