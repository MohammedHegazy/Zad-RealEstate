<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('statistics', [DashboardController::class, 'statistics'])->name('statistics');
