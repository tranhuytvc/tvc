<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Public: QR welcome & scan/display
Route::get('/welcome/{qrCode}', [WelcomeController::class, 'show'])->name('welcome');
Route::get('/scan',             [WelcomeController::class, 'scan'])->name('scan');
Route::get('/display',          [WelcomeController::class, 'display'])->name('display');
Route::get('/scan/{slug}',      [WelcomeController::class, 'scan'])->name('scan.station');
Route::get('/display/{slug}',   [WelcomeController::class, 'display'])->name('display.station');

// Auth
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Statistics (auth required)
Route::get('/stats', [StatsController::class, 'index'])->name('stats')->middleware(['auth', 'permission:stats.view']);

// CMS
Route::prefix('cms')->name('cms.')->middleware('auth')->group(function () {

    // Guests
    Route::get('/',                    [GuestController::class, 'index'])->name('index')->middleware('permission:guests.view');
    Route::get('/create',              [GuestController::class, 'create'])->name('create')->middleware('permission:guests.create');
    Route::post('/',                   [GuestController::class, 'store'])->name('store')->middleware('permission:guests.create');
    Route::get('/download-all-qr',     [GuestController::class, 'downloadAllQr'])->name('download-all-qr')->middleware('permission:guests.export');
    Route::delete('/destroy-all',      [GuestController::class, 'destroyAll'])->name('destroy-all')->middleware('permission:guests.delete');
    Route::delete('/destroy-selected', [GuestController::class, 'destroySelected'])->name('destroy-selected')->middleware('permission:guests.delete');

    Route::get('/{guest}/edit',        [GuestController::class, 'edit'])->name('edit')->middleware('permission:guests.edit');
    Route::put('/{guest}',             [GuestController::class, 'update'])->name('update')->middleware('permission:guests.edit');
    Route::delete('/{guest}',          [GuestController::class, 'destroy'])->name('destroy')->middleware('permission:guests.delete');
    Route::get('/{guest}/download-qr', [GuestController::class, 'downloadQr'])->name('download-qr')->middleware('permission:guests.export');
    Route::post('/{guest}/toggle-lock',[GuestController::class, 'toggleLock'])->name('toggle-lock')->middleware('permission:guests.lock');
    Route::post('/{guest}/reset-scans',[GuestController::class, 'resetScans'])->name('reset-scans')->middleware('permission:guests.lock');

    // Stations
    Route::prefix('stations')->name('stations.')->group(function () {
        Route::get('/',               [StationController::class, 'index'])->name('index')->middleware('permission:stations.view');
        Route::get('/create',         [StationController::class, 'create'])->name('create')->middleware('permission:stations.manage');
        Route::post('/',              [StationController::class, 'store'])->name('store')->middleware('permission:stations.manage');
        Route::get('/{station}/edit', [StationController::class, 'edit'])->name('edit')->middleware('permission:stations.manage');
        Route::put('/{station}',      [StationController::class, 'update'])->name('update')->middleware('permission:stations.manage');
        Route::delete('/{station}',   [StationController::class, 'destroy'])->name('destroy')->middleware('permission:stations.manage');
    });

    // Users management
    Route::prefix('users')->name('users.')->middleware('permission:users.manage')->group(function () {
        Route::get('/',            [UserController::class, 'index'])->name('index');
        Route::get('/create',      [UserController::class, 'create'])->name('create');
        Route::post('/',           [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',      [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',   [UserController::class, 'destroy'])->name('destroy');
    });

    // Roles management
    Route::prefix('roles')->name('roles.')->middleware('permission:roles.manage')->group(function () {
        Route::get('/',            [RoleController::class, 'index'])->name('index');
        Route::get('/create',      [RoleController::class, 'create'])->name('create');
        Route::post('/',           [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}',      [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}',   [RoleController::class, 'destroy'])->name('destroy');
    });
});

Route::get('/', function () {
    return redirect()->route('scan');
});
