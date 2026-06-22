<?php

use App\Http\Controllers\Api\FavoriteEstateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Favorites — estates
|--------------------------------------------------------------------------
*/

Route::prefix('my/favorite-estates')->name('my.favorite-estates.')->group(function () {
    Route::get('/', [FavoriteEstateController::class, 'index'])->name('index');
    Route::post('/', [FavoriteEstateController::class, 'store'])->name('store');
    Route::get('check/{estate}', [FavoriteEstateController::class, 'check'])->name('check');
    Route::get('{favorite_estate}', [FavoriteEstateController::class, 'show'])->name('show');
    Route::delete('{favorite_estate}', [FavoriteEstateController::class, 'destroy'])->name('destroy');
});
