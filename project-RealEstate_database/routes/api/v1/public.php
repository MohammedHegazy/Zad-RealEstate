<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\EstateController;
use App\Http\Controllers\Api\EstateGeoController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\Trust\AgentReviewController;
use App\Http\Controllers\Api\Trust\CompanyReviewController;
use App\Http\Controllers\Api\Trust\PropertyReviewController;
use App\Http\Controllers\Api\Trust\TrustScoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public resources (no authentication required)
|--------------------------------------------------------------------------
*/

Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/', [CompanyController::class, 'index'])->name('index');
    Route::get('{company}/reviews/summary', [CompanyReviewController::class, 'summary'])->name('reviews.summary');
    Route::get('{company}/reviews', [CompanyReviewController::class, 'index'])->name('reviews.index');
    Route::get('{company}/trust-score', [TrustScoreController::class, 'forCompany'])->name('trust-score');
    Route::get('{company}', [CompanyController::class, 'show'])->name('show');
    Route::get('{company}/agents', [AgentController::class, 'indexByCompany'])->name('agents.index');
});

Route::prefix('agents')->name('agents.')->group(function () {
    Route::get('/', [AgentController::class, 'index'])->name('index');
    Route::get('{agent}/reviews/summary', [AgentReviewController::class, 'summary'])->name('reviews.summary');
    Route::get('{agent}/reviews', [AgentReviewController::class, 'index'])->name('reviews.index');
    Route::get('{agent}/trust-score', [TrustScoreController::class, 'forAgent'])->name('trust-score');
    Route::get('{agent}', [AgentController::class, 'show'])->name('show');
    Route::post('{agent}/share', [AgentController::class, 'share'])->name('share');
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
    Route::get('nearby', [EstateGeoController::class, 'nearby'])->name('nearby');
    Route::get('in-radius', [EstateGeoController::class, 'inRadius'])->name('in-radius');
    Route::get('map', [EstateGeoController::class, 'map'])->name('map');
    Route::get('/', [EstateController::class, 'index'])->name('index');
    Route::get('{estate}/reviews/summary', [PropertyReviewController::class, 'summary'])->name('reviews.summary');
    Route::get('{estate}/reviews', [PropertyReviewController::class, 'index'])->name('reviews.index');
    Route::get('{estate}', [EstateController::class, 'show'])->name('show');
    Route::post('{estate}/share', [EstateController::class, 'share'])->name('share');
});

Route::get('social-links/{socialLink}', [SocialLinkController::class, 'show'])->name('social-links.show');
Route::get('social-media/{socialLink}', [SocialLinkController::class, 'show'])->name('social-media.show');
