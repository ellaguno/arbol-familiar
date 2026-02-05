<?php

use Illuminate\Support\Facades\Route;
use Plugin\ResearchAssistant\Controllers\ResearchController;
use Plugin\ResearchAssistant\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Research Assistant Plugin Routes
|--------------------------------------------------------------------------
*/

// Public routes (authenticated users)
Route::middleware(['web', 'auth'])->prefix('research')->name('research.')->group(function () {
    Route::get('/', [ResearchController::class, 'index'])->name('index');
    Route::get('/person/{person}', [ResearchController::class, 'searchPerson'])->name('person');
    Route::post('/search', [ResearchController::class, 'search'])->name('search');
    Route::get('/{session}', [ResearchController::class, 'session'])->name('session');
    Route::get('/{session}/status', [ResearchController::class, 'status'])->name('status');
    Route::delete('/{session}', [ResearchController::class, 'destroy'])->name('destroy');
});

// Admin routes
Route::middleware(['web', 'auth', 'admin'])->prefix('admin/research')->name('admin.research.')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/test-provider', [SettingsController::class, 'testProvider'])->name('test-provider');
});
