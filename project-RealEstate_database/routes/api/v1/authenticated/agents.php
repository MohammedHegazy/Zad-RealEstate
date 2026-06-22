<?php

use App\Http\Controllers\Api\AgentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Agents — profile & company staff
|--------------------------------------------------------------------------
| Agent ratings/reviews: use trust routes (POST agents/{agent}/reviews).
|--------------------------------------------------------------------------
*/

Route::get('my/agent', [AgentController::class, 'myProfile'])->name('my.agent');
Route::put('my/agent', [AgentController::class, 'updateMyProfile'])->name('my.agent.update');
