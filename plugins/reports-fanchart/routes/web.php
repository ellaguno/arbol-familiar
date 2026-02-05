<?php

use Illuminate\Support\Facades\Route;
use Plugin\ReportsFanchart\Controllers\FanChartController;

Route::middleware(['web', 'auth', 'verified'])->prefix('reports')->group(function () {
    Route::get('/fanchart/{person}', [FanChartController::class, 'show'])->name('reports.fanchart');
    Route::get('/fanchart/{person}/svg', [FanChartController::class, 'svg'])->name('reports.fanchart.svg');
    Route::get('/fanchart/{person}/pdf', [FanChartController::class, 'pdf'])->name('reports.fanchart.pdf');
});
