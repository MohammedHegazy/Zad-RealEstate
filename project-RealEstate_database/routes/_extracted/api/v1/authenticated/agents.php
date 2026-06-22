<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AgentRateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Agents — profile, ratings & company staff
|--------------------------------------------------------------------------
*/

Route::get('my/agent', [AgentController::class, 'myProfile'])->name('my.agent');
Route::put('my/agent', [AgentController::class, 'updateMyProfile'])->name('my.agent.update');

Route::prefix('my/agent-ratings')->name('my.agent-ratings.')->group(function () {
    Route::get('/', [AgentRateController::class, 'index'])->name('index');
    Route::post('/', [AgentRateController::class, 'store'])->name('store');
    Route::get('{agent_rate}', [AgentRateController::class, 'show'])->name('show');
    Route::put('{agent_rate}', [AgentRateController::class, 'update'])->name('update');
    Route::delete('{agent_rate}', [AgentRateController::class, 'destroy'])->name('destroy');
});

Route::prefix('agents/{agent}')->name('agents.')->group(function () {
    Route::get('ratings/me', [AgentRateController::class, 'myRatingForAgent'])->name('ratings.me');
    Route::post('rate', [AgentRateController::class, 'storeForAgent'])->name('rate');
});
