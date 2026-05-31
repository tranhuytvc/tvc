<?php

use App\Http\Controllers\GuestController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// Welcome / QR scan result
Route::get('/welcome/{token}', [WelcomeController::class, 'show'])->name('welcome');

// QR Scanner page
Route::get('/scan', [WelcomeController::class, 'scan'])->name('scan');

// Display page
Route::get('/display', [WelcomeController::class, 'display'])->name('display');

// Statistics
Route::get('/stats', [StatsController::class, 'index'])->name('stats');

// CMS
Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('/', [GuestController::class, 'index'])->name('index');
    Route::get('/create', [GuestController::class, 'create'])->name('create');
    Route::post('/', [GuestController::class, 'store'])->name('store');
    Route::get('/{guest}/edit', [GuestController::class, 'edit'])->name('edit');
    Route::put('/{guest}', [GuestController::class, 'update'])->name('update');
    Route::delete('/{guest}', [GuestController::class, 'destroy'])->name('destroy');
    Route::get('/download-all-qr', [GuestController::class, 'downloadAllQr'])->name('download-all-qr');
    Route::get('/{guest}/download-qr', [GuestController::class, 'downloadQr'])->name('download-qr');
});

Route::get('/', function () {
    return redirect()->route('scan');
});
