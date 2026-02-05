<?php

use Illuminate\Support\Facades\Route;
use Plugin\ReportsDescendants\Controllers\DescendantReportController;

Route::middleware(['web', 'auth', 'verified'])->prefix('reports')->group(function () {
    Route::get('/descendants/{person}', [DescendantReportController::class, 'show'])->name('reports.descendants');
    Route::get('/descendants/{person}/pdf', [DescendantReportController::class, 'pdf'])->name('reports.descendants.pdf');
});
