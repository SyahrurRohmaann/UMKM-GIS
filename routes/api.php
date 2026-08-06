<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\GeoJsonController;

Route::prefix('ahp')->group(function () {
    Route::post('/calculate', [CalculationController::class, 'calculate']);
});

Route::prefix('geojson')->group(function () {
    Route::get('/kelurahan', [GeoJsonController::class, 'kelurahan']);
    Route::get('/alternatif', [GeoJsonController::class, 'alternatif']);
});
