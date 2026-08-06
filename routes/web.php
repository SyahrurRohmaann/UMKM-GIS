<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AlternatifLokasiController;
use App\Http\Controllers\Admin\KelurahanController;
use App\Http\Controllers\Admin\KriteriaController;

Route::get('/', function () {
    return redirect()->route('map.index');
});

Route::get('/map', [MapController::class, 'index'])->name('map.index');

// Auth Admin
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Panel (Protected)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // CRUD Alternatif Lokasi
    Route::resource('alternatif', AlternatifLokasiController::class);
    
    // CRUD Kelurahan
    Route::resource('kelurahan', KelurahanController::class);
    
    // CRUD Kriteria (Hanya Edit biasanya, sesuai PRD)
    Route::resource('kriteria', KriteriaController::class)->only(['index', 'edit', 'update']);
});
