<?php

use App\Http\Controllers\Api\Admin\SocialMediaController;
use Illuminate\Support\Facades\Route;

Route::apiResource('social-media', SocialMediaController::class);
