<?php

use Illuminate\Support\Facades\Route;
use Plugin\SocialLogin\Controllers\SocialAuthController;
use Plugin\SocialLogin\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Social Login Plugin Routes
|--------------------------------------------------------------------------
*/

// OAuth routes (public, no auth required)
Route::middleware(['web'])->prefix('auth/social')->name('social.')->group(function () {
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirectToProvider'])
        ->name('redirect')
        ->whereIn('provider', ['google', 'microsoft', 'facebook']);

    Route::get('/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
        ->name('callback')
        ->whereIn('provider', ['google', 'microsoft', 'facebook']);
});

// Admin settings routes
Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/social-login')
    ->name('admin.social-login.')
    ->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
