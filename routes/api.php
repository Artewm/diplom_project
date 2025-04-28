<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\LibraryController;

Route::apiResource('tracks', TrackController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/library', [LibraryController::class, 'index']);
    Route::post('/library/tracks/{track}/favorite', [LibraryController::class, 'toggleFavorite']);
});
