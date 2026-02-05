<?php

use Illuminate\Support\Facades\Route;
use Plugin\ReportsAncestors\Controllers\AncestorReportController;

Route::middleware(['web', 'auth', 'verified'])->prefix('reports')->group(function () {
    Route::get('/ancestors/{person}', [AncestorReportController::class, 'show'])->name('reports.ancestors');
    Route::get('/ancestors/{person}/pdf', [AncestorReportController::class, 'pdf'])->name('reports.ancestors.pdf');
});
