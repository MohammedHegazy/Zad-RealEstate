<?php

use App\Http\Controllers\Api\Admin\SocialLinkController;
use Illuminate\Support\Facades\Route;

Route::apiResource('social-links', SocialLinkController::class);
