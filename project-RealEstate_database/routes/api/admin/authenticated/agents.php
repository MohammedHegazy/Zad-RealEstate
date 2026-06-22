<?php

use App\Http\Controllers\Api\Admin\AgentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('agents', AgentController::class);
Route::post('agents/{agent}', [AgentController::class, 'update'])->name('agents.update-media');
