<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\User\StoreController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\GetController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SearchController;


// Публичный маршрут для получения всех треков
Route::get('tracks', [TrackController::class, 'index']);

// Защищенные маршруты для работы с треками
Route::group([
    'middleware' => ['api', 'auth:api']
], function ($router) {
    Route::get('tracks/{track}', [TrackController::class, 'show']);
    Route::put('tracks/{track}', [TrackController::class, 'update']);
    Route::delete('tracks/{track}', [TrackController::class, 'destroy']);
});

// Отдельный маршрут для загрузки трека (multipart/form-data)
Route::group([
    'middleware' => ['auth:api']
], function ($router) {
    Route::post('tracks', [TrackController::class, 'store']);
});

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

// Маршруты для работы с плейлистами (требуется JWT токен)
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'playlists'
], function ($router) {
    Route::get('/', [PlaylistController::class, 'getUserPlaylists']);
    Route::post('/', [PlaylistController::class, 'create']);
    Route::get('/{id}', [PlaylistController::class, 'show']);
    Route::put('/{id}', [PlaylistController::class, 'update']);
    Route::delete('/{id}', [PlaylistController::class, 'delete']);
    Route::post('/{id}/tracks', [PlaylistController::class, 'addTrack']);
    Route::delete('/{id}/tracks', [PlaylistController::class, 'removeTrack']);
});

// Маршруты для работы с избранными треками (требуется JWT токен)
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'favorites'
], function ($router) {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/{trackId}', [FavoriteController::class, 'add']);
    Route::delete('/{trackId}', [FavoriteController::class, 'remove']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/library', [LibraryController::class, 'index']);
});
// Route::group(['namespace' => 'User', 'prefix' => 'users'], function () {
//     Route::post('/', 'StoreController');
// });
Route::post('/registration', [\App\Http\Controllers\User\StoreController::class, '__invoke']);

// Маршрут для получения данных пользователя по ID
Route::middleware(['api', 'auth:api'])->get('/users/{id}', [\App\Http\Controllers\User\GetController::class, '__invoke']);

// Публичный маршрут для поиска треков и исполнителей
Route::get('search', [\App\Http\Controllers\SearchController::class, 'search']);