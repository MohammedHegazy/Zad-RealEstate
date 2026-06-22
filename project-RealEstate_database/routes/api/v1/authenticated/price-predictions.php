<?php

use App\Http\Controllers\Api\PricePredictionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Price predictions — estate and ad-hoc preview
|--------------------------------------------------------------------------
*/

Route::prefix('price-predictions')->name('price-predictions.')->group(function () {
    Route::post('preview', [PricePredictionController::class, 'preview'])->name('preview');
});
