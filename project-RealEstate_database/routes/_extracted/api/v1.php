<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — Real Estate Platform
|--------------------------------------------------------------------------
|
| Base URL: /api/v1
| Route names are prefixed with "api."
|
*/

Route::prefix('v1')->name('api.')->group(function () {

    require __DIR__.'/v1/auth.php';
    require __DIR__.'/v1/public.php';

    Route::middleware('auth:sanctum')->group(function () {
        require __DIR__.'/v1/authenticated/profile.php';
        require __DIR__.'/v1/authenticated/estates.php';
        require __DIR__.'/v1/authenticated/favorites.php';
        require __DIR__.'/v1/authenticated/investment-analyses.php';
        require __DIR__.'/v1/authenticated/companies.php';
        require __DIR__.'/v1/authenticated/agents.php';
        require __DIR__.'/v1/authenticated/notifications.php';
        require __DIR__.'/v1/authenticated/messages.php';
        require __DIR__.'/v1/authenticated/social-media.php';
    });
});
