<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\User\StoreController;

Route::apiResource('tracks', TrackController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/library', [LibraryController::class, 'index']);
});
// Route::group(['namespace' => 'User', 'prefix' => 'users'], function () {
//     Route::post('/', 'StoreController');
// });
Route::post('/registration', [\App\Http\Controllers\User\StoreController::class, '__invoke']);