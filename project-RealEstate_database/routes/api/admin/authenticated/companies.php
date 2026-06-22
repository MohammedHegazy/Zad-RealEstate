<?php

use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\CompanySocialLinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/', [CompanyController::class, 'index'])->name('index');
    Route::patch('{company}/status', [CompanyController::class, 'updateStatus'])->name('status');
    Route::get('{company}', [CompanyController::class, 'show'])->name('show');
    Route::put('{company}', [CompanyController::class, 'update'])->name('update');
    Route::post('{company}', [CompanyController::class, 'update'])->name('update-media');
    Route::delete('{company}', [CompanyController::class, 'destroy'])->name('destroy');
    Route::post('/', [CompanyController::class, 'store'])->name('store');
});

Route::prefix('companies/{company}/social-links')->name('companies.social-links.')->group(function () {
    Route::get('/', [CompanySocialLinkController::class, 'index'])->name('index');
    Route::post('/', [CompanySocialLinkController::class, 'store'])->name('store');
    Route::put('{socialLink}', [CompanySocialLinkController::class, 'update'])->name('update');
    Route::delete('{socialLink}', [CompanySocialLinkController::class, 'destroy'])->name('destroy');
});
