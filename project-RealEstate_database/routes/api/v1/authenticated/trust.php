<?php

use App\Http\Controllers\Api\Trust\AgentReviewController;
use App\Http\Controllers\Api\Trust\CompanyReviewController;
use App\Http\Controllers\Api\Trust\PropertyReviewController;
use App\Http\Controllers\Api\Trust\TrustScoreController;
use App\Http\Controllers\Api\Trust\VerificationRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trust & credibility — authenticated actions
|--------------------------------------------------------------------------
*/

Route::prefix('my/property-reviews')->name('my.property-reviews.')->group(function () {
    Route::put('{propertyReview}', [PropertyReviewController::class, 'update'])->name('update');
    Route::delete('{propertyReview}', [PropertyReviewController::class, 'destroy'])->name('destroy');
});

Route::prefix('my/agent-reviews')->name('my.agent-reviews.')->group(function () {
    Route::get('/', [AgentReviewController::class, 'indexMine'])->name('index');
    Route::put('{agentReview}', [AgentReviewController::class, 'update'])->name('update');
    Route::delete('{agentReview}', [AgentReviewController::class, 'destroy'])->name('destroy');
});

Route::prefix('my/company-reviews')->name('my.company-reviews.')->group(function () {
    Route::put('{companyReview}', [CompanyReviewController::class, 'update'])->name('update');
    Route::delete('{companyReview}', [CompanyReviewController::class, 'destroy'])->name('destroy');
});

Route::prefix('my/verification-requests')->name('my.verification-requests.')->group(function () {
    Route::get('/', [VerificationRequestController::class, 'index'])->name('index');
    Route::post('/', [VerificationRequestController::class, 'store'])->name('store');
});

Route::post('estates/{estate}/reviews', [PropertyReviewController::class, 'store'])
    ->name('estates.reviews.store');
Route::get('estates/{estate}/reviews/me', [PropertyReviewController::class, 'myReviewForEstate'])
    ->name('estates.reviews.me');
Route::post('agents/{agent}/reviews', [AgentReviewController::class, 'store'])
    ->name('agents.reviews.store');
Route::get('agents/{agent}/reviews/me', [AgentReviewController::class, 'myReviewForAgent'])
    ->name('agents.reviews.me');
Route::post('companies/{company}/reviews', [CompanyReviewController::class, 'store'])
    ->name('companies.reviews.store');
Route::get('companies/{company}/reviews/me', [CompanyReviewController::class, 'myReviewForCompany'])
    ->name('companies.reviews.me');
