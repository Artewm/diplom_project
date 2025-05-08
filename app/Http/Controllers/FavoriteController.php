<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /**
     * Получить избранные треки текущего пользователя.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован'], 401);
        }
        
        // Получаем полные данные о треках, которые находятся в избранном у пользователя
        $favoriteTracks = Track::join('favorites', 'tracks.id', '=', 'favorites.track_id')
            ->where('favorites.user_id', $user->id)
            ->select('tracks.*') // Выбираем только данные из таблицы треков
            ->get();
        
        return response()->json($favoriteTracks);
    }

    /**
     * Добавить трек в избранное.
     *
     * @param int $trackId ID трека
     * @return JsonResponse
     */
    public function add($trackId): JsonResponse
    {
        $user = Auth::user();
        
        // Проверяем существование трека
        $track = Track::find($trackId);
        if (!$track) {
            return response()->json(['message' => 'Трек не найден'], 404);
        }
        
        // Проверяем, не добавлен ли трек уже в избранное
        $exists = Favorite::where('user_id', $user->id)
            ->where('track_id', $trackId)
            ->exists();
            
        if ($exists) {
            return response()->json(['message' => 'Трек уже в избранном'], 409);
        }
        
        // Добавляем в избранное
        Favorite::create([
            'user_id' => $user->id,
            'track_id' => $trackId
        ]);
        
        return response()->json(['message' => 'Трек добавлен в избранное']);
    }

    /**
     * Удалить трек из избранного.
     *
     * @param int $trackId ID трека
     * @return JsonResponse
     */
    public function remove($trackId): JsonResponse
    {
        $user = Auth::user();
        
        $deleted = Favorite::where('user_id', $user->id)
            ->where('track_id', $trackId)
            ->delete();
            
        if ($deleted) {
            return response()->json(['message' => 'Трек удален из избранного']);
        }
        
        return response()->json(['message' => 'Трек не был в избранном'], 404);
    }
} 