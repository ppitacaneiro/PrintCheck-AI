<?php

use App\Http\Controllers\PrintAnalysisController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('landing');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // API de análisis de preimpresión
    Route::prefix('api/print-analyses')->name('print-analyses.')->group(function () {
        Route::get('/',               [PrintAnalysisController::class, 'index'])->name('index');
        Route::post('/',              [PrintAnalysisController::class, 'store'])->name('store');
        Route::get('/stats',          [PrintAnalysisController::class, 'stats'])->name('stats');
        Route::get('/{printAnalysis}',[PrintAnalysisController::class, 'show'])->name('show');
    });
});
