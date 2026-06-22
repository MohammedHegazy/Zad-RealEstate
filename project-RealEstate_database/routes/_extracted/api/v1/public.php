<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AgentRateController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\EstateController;
use App\Http\Controllers\Api\PlaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public resources (no authentication required)
|--------------------------------------------------------------------------
*/

Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/', [CompanyController::class, 'index'])->name('index');
    Route::get('{company}', [CompanyController::class, 'show'])->name('show');
    Route::get('{company}/agents', [AgentController::class, 'indexByCompany'])->name('agents.index');
});

Route::prefix('agents')->name('agents.')->group(function () {
    Route::get('/', [AgentController::class, 'index'])->name('index');
    Route::get('{agent}', [AgentController::class, 'show'])->name('show');
    Route::post('{agent}/share', [AgentController::class, 'share'])->name('share');

    Route::prefix('{agent}/ratings')->name('ratings.')->group(function () {
        Route::get('/', [AgentRateController::class, 'indexForAgent'])->name('index');
        Route::get('summary', [AgentRateController::class, 'summary'])->name('summary');
    });
});

Route::prefix('cities')->name('cities.')->group(function () {
    Route::get('/', [CityController::class, 'index'])->name('index');
    Route::get('{city}', [CityController::class, 'show'])->name('show');
    Route::get('{city}/places', [PlaceController::class, 'indexByCity'])->name('places.index');
});

Route::prefix('places')->name('places.')->group(function () {
    Route::get('/', [PlaceController::class, 'index'])->name('index');
    Route::get('{place}', [PlaceController::class, 'show'])->name('show');
});

Route::prefix('estates')->name('estates.')->group(function () {
    Route::get('/', [EstateController::class, 'index'])->name('index');
    Route::get('{estate}', [EstateController::class, 'show'])->name('show');
    Route::post('{estate}/share', [EstateController::class, 'share'])->name('share');
});
