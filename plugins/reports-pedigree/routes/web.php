<?php

use Illuminate\Support\Facades\Route;
use Plugin\ReportsPedigree\Controllers\PedigreeChartController;

Route::middleware(['web', 'auth', 'verified'])->prefix('reports')->group(function () {
    Route::get('/pedigree/{person}', [PedigreeChartController::class, 'show'])->name('reports.pedigree');
    Route::get('/pedigree/{person}/svg', [PedigreeChartController::class, 'svg'])->name('reports.pedigree.svg');
    Route::get('/pedigree/{person}/pdf', [PedigreeChartController::class, 'pdf'])->name('reports.pedigree.pdf');
});
