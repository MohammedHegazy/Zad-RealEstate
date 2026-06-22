<?php

use App\Http\Controllers\Api\Admin\CityController;
use App\Http\Controllers\Api\Admin\PlaceController;
use Illuminate\Support\Facades\Route;

Route::apiResource('cities', CityController::class);
Route::post('cities/{city}', [CityController::class, 'update'])->name('cities.update-media');

Route::apiResource('places', PlaceController::class);
