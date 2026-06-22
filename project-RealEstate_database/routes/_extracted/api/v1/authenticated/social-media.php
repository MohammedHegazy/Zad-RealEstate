<?php

use App\Http\Controllers\Api\SocialMediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Social media links
|--------------------------------------------------------------------------
*/

Route::get('my/social-media', [SocialMediaController::class, 'myLinks'])->name('my.social-media');
Route::put('my/social-media', [SocialMediaController::class, 'updateMyLinks'])->name('my.social-media.update');
Route::get('social-media/{socialMedia}', [SocialMediaController::class, 'show'])->name('social-media.show');
