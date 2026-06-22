<?php

use App\Http\Controllers\Api\FavoriteAgentController;
use App\Http\Controllers\Api\FavoriteEstateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Favorites — estates & agents
|--------------------------------------------------------------------------
*/

Route::prefix('my/favorite-estates')->name('my.favorite-estates.')->group(function () {
    Route::get('/', [FavoriteEstateController::class, 'index'])->name('index');
    Route::post('/', [FavoriteEstateController::class, 'store'])->name('store');
    Route::get('check/{estate}', [FavoriteEstateController::class, 'check'])->name('check');
    Route::get('{favorite_estate}', [FavoriteEstateController::class, 'show'])->name('show');
    Route::delete('{favorite_estate}', [FavoriteEstateController::class, 'destroy'])->name('destroy');
});

Route::prefix('my/favorite-agents')->name('my.favorite-agents.')->group(function () {
    Route::get('/', [FavoriteAgentController::class, 'index'])->name('index');
    Route::post('/', [FavoriteAgentController::class, 'store'])->name('store');
    Route::get('check/{agent}', [FavoriteAgentController::class, 'check'])->name('check');
    Route::get('{favorite_agent}', [FavoriteAgentController::class, 'show'])->name('show');
    Route::delete('{favorite_agent}', [FavoriteAgentController::class, 'destroy'])->name('destroy');
});

Route::prefix('agents/{agent}')->name('agents.')->group(function () {
    Route::post('favorite', [FavoriteAgentController::class, 'storeByAgent'])->name('favorite');
    Route::delete('favorite', [FavoriteAgentController::class, 'destroyByAgent'])->name('unfavorite');
});
