<?php

use Illuminate\Support\Facades\Route;
use Plugin\PhotoBanner\Controllers\SettingsController;

// Admin routes
Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/photo-banner')
    ->name('admin.photo-banner.')
    ->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
