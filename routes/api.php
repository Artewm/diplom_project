<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\User\StoreController;
use App\Http\Controllers\AuthController;


Route::apiResource('tracks', TrackController::class);

// Публичные маршруты аутентификации
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    // Route::get('login', [AuthController::class, 'loginGet']);
    Route::post('login', [AuthController::class, 'login']);
});

// Защищённые маршруты аутентификации (требуется JWT токен)
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'auth'
], function ($router) {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/library', [LibraryController::class, 'index']);
});
// Route::group(['namespace' => 'User', 'prefix' => 'users'], function () {
//     Route::post('/', 'StoreController');
// });
Route::post('/registration', [\App\Http\Controllers\User\StoreController::class, '__invoke']);