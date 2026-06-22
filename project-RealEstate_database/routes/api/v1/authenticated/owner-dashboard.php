<?php

use App\Http\Controllers\Api\Owner\OwnerDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('my/owner-dashboard', [OwnerDashboardController::class, 'summary'])
    ->name('my.owner-dashboard');
