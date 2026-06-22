<?php

use App\Http\Controllers\Api\Admin\TrustModerationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin — trust & credibility moderation
|--------------------------------------------------------------------------
*/

Route::prefix('trust')->name('trust.')->group(function () {
    Route::get('reviews', [TrustModerationController::class, 'indexReviews'])->name('reviews.index');
    Route::get('reviews/pending', [TrustModerationController::class, 'pendingReviews'])->name('reviews.pending');

    Route::get('property-reviews/{propertyReview}', [TrustModerationController::class, 'showPropertyReview'])
        ->name('property-reviews.show');
    Route::get('agent-reviews/{agentReview}', [TrustModerationController::class, 'showAgentReview'])
        ->name('agent-reviews.show');
    Route::get('company-reviews/{companyReview}', [TrustModerationController::class, 'showCompanyReview'])
        ->name('company-reviews.show');

    Route::get('verifications', [TrustModerationController::class, 'indexVerifications'])->name('verifications.index');
    Route::get('verifications/pending', [TrustModerationController::class, 'pendingVerifications'])
        ->name('verifications.pending');
    Route::get('verifications/{verificationRequest}', [TrustModerationController::class, 'showVerification'])
        ->name('verifications.show');
    Route::get('verifications/{verificationRequest}/document', [TrustModerationController::class, 'downloadVerificationDocument'])
        ->name('verifications.document');

    Route::post('property-reviews/{propertyReview}/approve', [TrustModerationController::class, 'approvePropertyReview'])
        ->name('property-reviews.approve');
    Route::post('property-reviews/{propertyReview}/reject', [TrustModerationController::class, 'rejectPropertyReview'])
        ->name('property-reviews.reject');
    Route::delete('property-reviews/{propertyReview}', [TrustModerationController::class, 'deletePropertyReview'])
        ->name('property-reviews.destroy');

    Route::post('agent-reviews/{agentReview}/approve', [TrustModerationController::class, 'approveAgentReview'])
        ->name('agent-reviews.approve');
    Route::post('agent-reviews/{agentReview}/reject', [TrustModerationController::class, 'rejectAgentReview'])
        ->name('agent-reviews.reject');
    Route::delete('agent-reviews/{agentReview}', [TrustModerationController::class, 'deleteAgentReview'])
        ->name('agent-reviews.destroy');

    Route::post('company-reviews/{companyReview}/approve', [TrustModerationController::class, 'approveCompanyReview'])
        ->name('company-reviews.approve');
    Route::post('company-reviews/{companyReview}/reject', [TrustModerationController::class, 'rejectCompanyReview'])
        ->name('company-reviews.reject');
    Route::delete('company-reviews/{companyReview}', [TrustModerationController::class, 'deleteCompanyReview'])
        ->name('company-reviews.destroy');

    Route::post('verifications/{verificationRequest}/approve', [TrustModerationController::class, 'approveVerification'])
        ->name('verifications.approve');
    Route::post('verifications/{verificationRequest}/reject', [TrustModerationController::class, 'rejectVerification'])
        ->name('verifications.reject');

    Route::post('agents/{agent}/recalculate-trust', [TrustModerationController::class, 'recalculateAgentTrust'])
        ->name('agents.recalculate-trust');
    Route::post('companies/{company}/recalculate-trust', [TrustModerationController::class, 'recalculateCompanyTrust'])
        ->name('companies.recalculate-trust');
});
