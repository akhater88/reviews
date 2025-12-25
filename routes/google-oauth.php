<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Google OAuth Routes
|--------------------------------------------------------------------------
|
| Add these routes to your web.php file
|
*/

Route::middleware(['auth'])->group(function () {
    // Google OAuth callback
    Route::get('/auth/google/callback', [GoogleOAuthController::class, 'callback'])
        ->name('google.oauth.callback');

    // Disconnect Google account
    Route::post('/auth/google/disconnect', [GoogleOAuthController::class, 'disconnect'])
        ->name('google.oauth.disconnect');
});
