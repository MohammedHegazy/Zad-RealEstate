<?php

use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\CompanySocialMediaController;
use Illuminate\Support\Facades\Route;

Route::apiResource('companies', CompanyController::class);

Route::prefix('companies/{company}/social-links')->name('companies.social-links.')->group(function () {
    Route::get('/', [CompanySocialMediaController::class, 'index'])->name('index');
    Route::post('/', [CompanySocialMediaController::class, 'store'])->name('store');
    Route::put('{socialLink}', [CompanySocialMediaController::class, 'update'])->name('update');
    Route::delete('{socialLink}', [CompanySocialMediaController::class, 'destroy'])->name('destroy');
});
