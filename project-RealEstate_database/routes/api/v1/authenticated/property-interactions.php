<?php

use App\Http\Controllers\Api\PropertyInteractionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Property interactions — behavioral tracking for recommendations
|--------------------------------------------------------------------------
*/

Route::prefix('my/property-interactions')->name('my.property-interactions.')->group(function () {
    Route::get('/', [PropertyInteractionController::class, 'index'])->name('index');
    Route::post('/', [PropertyInteractionController::class, 'store'])->name('store');
    Route::get('insights', [PropertyInteractionController::class, 'insights'])->name('insights');
});
