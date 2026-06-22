<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User profile
|--------------------------------------------------------------------------
*/

Route::get('me', [UserController::class, 'me'])->name('me');
Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');
Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
