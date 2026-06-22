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

    Route::middleware(['auth:sanctum', 'activity'])->group(function () {
        Route::get('users/{user}/online', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'online' => $user->isOnline(),
                    'last_seen_ago' => $user->lastSeenAgo(),
                ],
            ]);
        })->name('users.online');

        require __DIR__.'/v1/authenticated/profile.php';
        require __DIR__.'/v1/authenticated/estates.php';
        require __DIR__.'/v1/authenticated/favorites.php';
        require __DIR__.'/v1/authenticated/property-interactions.php';
        require __DIR__.'/v1/authenticated/investment-analyses.php';
        require __DIR__.'/v1/authenticated/investment-portfolios.php';
        require __DIR__.'/v1/authenticated/investor-dashboard.php';
        require __DIR__.'/v1/authenticated/price-predictions.php';
        require __DIR__.'/v1/authenticated/market-analytics.php';
        require __DIR__.'/v1/authenticated/companies.php';
        require __DIR__.'/v1/authenticated/agents.php';
        require __DIR__.'/v1/authenticated/notifications.php';
        require __DIR__.'/v1/authenticated/messages.php';
        require __DIR__.'/v1/authenticated/social-links.php';
        require __DIR__.'/v1/authenticated/preferences.php';
        require __DIR__.'/v1/authenticated/trust.php';
        require __DIR__.'/v1/authenticated/owner-dashboard.php';
    });
});
