<?php

use App\Http\Controllers\GuestController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// QR welcome - routes by UUID (non-guessable, stable)
Route::get('/welcome/{qrCode}', [WelcomeController::class, 'show'])->name('welcome');

// Scan/display - default (no station)
Route::get('/scan', [WelcomeController::class, 'scan'])->name('scan');
Route::get('/display', [WelcomeController::class, 'display'])->name('display');

// Scan/display - station-specific
Route::get('/scan/{slug}', [WelcomeController::class, 'scan'])->name('scan.station');
Route::get('/display/{slug}', [WelcomeController::class, 'display'])->name('display.station');

// Statistics
Route::get('/stats', [StatsController::class, 'index'])->name('stats');

// CMS
Route::prefix('cms')->name('cms.')->group(function () {
    // Guests
    Route::get('/', [GuestController::class, 'index'])->name('index');
    Route::get('/create', [GuestController::class, 'create'])->name('create');
    Route::post('/', [GuestController::class, 'store'])->name('store');
    Route::get('/download-all-qr', [GuestController::class, 'downloadAllQr'])->name('download-all-qr');
    Route::get('/{guest}/edit', [GuestController::class, 'edit'])->name('edit');
    Route::put('/{guest}', [GuestController::class, 'update'])->name('update');
    Route::delete('/{guest}', [GuestController::class, 'destroy'])->name('destroy');
    Route::get('/{guest}/download-qr', [GuestController::class, 'downloadQr'])->name('download-qr');
    Route::post('/{guest}/toggle-lock', [GuestController::class, 'toggleLock'])->name('toggle-lock');
    Route::post('/{guest}/reset-scans', [GuestController::class, 'resetScans'])->name('reset-scans');

    // Stations
    Route::prefix('stations')->name('stations.')->group(function () {
        Route::get('/', [StationController::class, 'index'])->name('index');
        Route::get('/create', [StationController::class, 'create'])->name('create');
        Route::post('/', [StationController::class, 'store'])->name('store');
        Route::get('/{station}/edit', [StationController::class, 'edit'])->name('edit');
        Route::put('/{station}', [StationController::class, 'update'])->name('update');
        Route::delete('/{station}', [StationController::class, 'destroy'])->name('destroy');
    });
});

Route::get('/', function () {
    return redirect()->route('scan');
});
