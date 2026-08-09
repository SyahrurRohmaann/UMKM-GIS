<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\GeoJsonController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\LocationController;

Route::prefix('ahp')->group(function () {
    Route::post('/calculate', [CalculationController::class, 'calculate']);
});

Route::prefix('geojson')->group(function () {
    Route::get('/kelurahan', [GeoJsonController::class, 'kelurahan']);
    Route::get('/alternatif', [GeoJsonController::class, 'alternatif']);
});

Route::post('/recommendations/generate', [RecommendationController::class, 'generate']);
Route::post('/locations/simulate-score', [RecommendationController::class, 'simulateCustomLocationScore']);
Route::get('/locations', [LocationController::class, 'index']);
Route::post('/locations/competitors-radius', [LocationController::class, 'competitorsWithinRadius']);
