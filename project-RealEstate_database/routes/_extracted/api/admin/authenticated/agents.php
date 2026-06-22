<?php

use App\Http\Controllers\Api\Admin\AgentController;
use App\Http\Controllers\Api\Admin\AgentRateController;
use Illuminate\Support\Facades\Route;

Route::apiResource('agents', AgentController::class);

Route::prefix('agent-ratings')->name('agent-ratings.')->group(function () {
    Route::get('/', [AgentRateController::class, 'index'])->name('index');
    Route::get('{agent_rate}', [AgentRateController::class, 'show'])->name('show');
    Route::delete('{agent_rate}', [AgentRateController::class, 'destroy'])->name('destroy');
});
