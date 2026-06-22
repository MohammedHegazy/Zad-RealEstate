<?php

use App\Http\Controllers\Api\SocialLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Social links (polymorphic)
|--------------------------------------------------------------------------
*/

Route::get('my/social-media', [SocialLinkController::class, 'myLinks'])->name('my.social-media');
Route::put('my/social-media', [SocialLinkController::class, 'updateMyLinks'])->name('my.social-media.update');
