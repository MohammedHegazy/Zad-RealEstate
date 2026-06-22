<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
|
| Base URL: /api/v1/admin
| Route names are prefixed with "admin."
|
*/

Route::prefix('v1/admin')->name('admin.')->group(function () {

    require __DIR__.'/admin/auth.php';

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        require __DIR__.'/admin/authenticated/dashboard.php';
        require __DIR__.'/admin/authenticated/users.php';
        require __DIR__.'/admin/authenticated/companies.php';
        require __DIR__.'/admin/authenticated/agents.php';
        require __DIR__.'/admin/authenticated/locations.php';
        require __DIR__.'/admin/authenticated/estates.php';
        require __DIR__.'/admin/authenticated/notifications.php';
        require __DIR__.'/admin/authenticated/messages.php';
        require __DIR__.'/admin/authenticated/social-media.php';
    });
});
