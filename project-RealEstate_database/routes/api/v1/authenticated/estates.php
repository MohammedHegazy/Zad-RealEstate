<?php

use App\Http\Controllers\Api\EstateAdController;
use App\Http\Controllers\Api\EstateController;
use App\Http\Controllers\Api\EstateImageController;
use App\Http\Controllers\Api\EstateVideoController;
use App\Http\Controllers\Api\FavoriteEstateController;
use App\Http\Controllers\Api\InvestmentAnalysisController;
use App\Http\Controllers\Api\PricePredictionController;
use App\Http\Controllers\Api\PropertyInteractionController;
use App\Http\Controllers\Api\SocialLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Estates — owner listings, media & estate actions
|--------------------------------------------------------------------------
*/

Route::get('my/estates', [EstateController::class, 'myEstates'])->name('my.estates');
Route::get('my/estates/{estate}', [EstateController::class, 'show'])->name('my.estates.show');
Route::post('my/estates', [EstateController::class, 'store'])->name('my.estates.store');
Route::match(['put', 'post'], 'my/estates/{estate}', [EstateController::class, 'update'])->name('my.estates.update');
Route::delete('my/estates/{estate}', [EstateController::class, 'destroy'])->name('my.estates.destroy');
Route::put('my/estates/{estate}/social-media', [SocialLinkController::class, 'updateEstateLinks'])
    ->name('my.estates.social-media');

Route::prefix('my/estates/{estate}')->name('my.estates.')->group(function () {
    Route::prefix('images')->name('images.')->group(function () {
        Route::get('/', [EstateImageController::class, 'index'])->name('index');
        Route::post('/', [EstateImageController::class, 'store'])->name('store');
        Route::patch('{image}/primary', [EstateImageController::class, 'setPrimary'])->name('primary');
        Route::delete('{image}', [EstateImageController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('videos')->name('videos.')->group(function () {
        Route::get('/', [EstateVideoController::class, 'index'])->name('index');
        Route::post('/', [EstateVideoController::class, 'store'])->name('store');
        Route::delete('{video}', [EstateVideoController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('ads')->name('ads.')->group(function () {
        Route::get('/', [EstateAdController::class, 'index'])->name('index');
        Route::post('/', [EstateAdController::class, 'store'])->name('store');
        Route::patch('{ad}/main', [EstateAdController::class, 'setMain'])->name('main');
        Route::delete('{ad}', [EstateAdController::class, 'destroy'])->name('destroy');
    });
});

Route::prefix('estates/{estate}')->name('estates.')->group(function () {
    Route::post('favorite', [FavoriteEstateController::class, 'storeByEstate'])->name('favorite');
    Route::delete('favorite', [FavoriteEstateController::class, 'destroyByEstate'])->name('unfavorite');
    Route::post('investment-analyses', [InvestmentAnalysisController::class, 'storeByEstate'])
        ->name('investment-analyses.store');
    Route::post('interactions', [PropertyInteractionController::class, 'storeForEstate'])
        ->name('interactions.store');
    Route::post('contact-agent', [PropertyInteractionController::class, 'contactAgent'])
        ->name('interactions.contact-agent');
    Route::post('price-prediction', [PricePredictionController::class, 'forEstate'])
        ->name('price-prediction');
});
