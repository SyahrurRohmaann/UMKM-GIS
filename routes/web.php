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
    
    // AHP Configuration for Jenis Usaha
    Route::get('ahp-config', [App\Http\Controllers\Admin\AhpConfigController::class, 'index'])->name('ahp_config.index');
    Route::post('ahp-config/save', [App\Http\Controllers\Admin\AhpConfigController::class, 'save'])->name('ahp_config.save');
});
