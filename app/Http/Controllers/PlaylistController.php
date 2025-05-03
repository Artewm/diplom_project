<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class PlaylistController extends Controller
{
    /**
     * Получить все плейлисты текущего пользователя
     */
    public function getUserPlaylists()
    {
        try {
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Получаем плейлисты пользователя с количеством треков
            $playlists = $user->playlists()
                ->withCount('tracks')
                ->get();
            
            return response()->json($playlists);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось получить плейлисты пользователя'], 500);
        }
    }
    
    /**
     * Создать новый плейлист
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'is_public' => 'boolean',
                'cover_image' => 'nullable|string'
            ]);
            
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Создаем новый плейлист
            $playlist = $user->playlists()->create([
                'name' => $request->name,
                'is_public' => $request->is_public ?? true,
                'cover_image' => $request->cover_image
            ]);
            
            return response()->json($playlist, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось создать плейлист', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Получить плейлист по ID
     */
    public function show($id)
    {
        try {
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Получаем плейлист с треками
            $playlist = $user->playlists()
                ->with('tracks')
                ->findOrFail($id);
            
            return response()->json($playlist);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Плейлист не найден'], 404);
        }
    }
    
    /**
     * Обновить плейлист
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'is_public' => 'sometimes|boolean',
                'cover_image' => 'nullable|string'
            ]);
            
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Находим плейлист и проверяем, принадлежит ли он пользователю
            $playlist = $user->playlists()->findOrFail($id);
            
            // Обновляем данные плейлиста
            $playlist->update($request->only(['name', 'is_public', 'cover_image']));
            
            return response()->json($playlist);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось обновить плейлист'], 500);
        }
    }
    
    /**
     * Удалить плейлист
     */
    public function delete($id)
    {
        try {
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Находим плейлист и проверяем, принадлежит ли он пользователю
            $playlist = $user->playlists()->findOrFail($id);
            
            // Удаляем плейлист
            $playlist->delete();
            
            return response()->json(['message' => 'Плейлист успешно удален']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось удалить плейлист'], 500);
        }
    }
    
    /**
     * Добавить трек в плейлист
     */
    public function addTrack(Request $request, $playlistId)
    {
        try {
            $request->validate([
                'track_id' => 'required|exists:tracks,id'
            ]);
            
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Находим плейлист
            $playlist = $user->playlists()->findOrFail($playlistId);
            
            // Добавляем трек в плейлист
            // Получаем текущую максимальную позицию в плейлисте
            $maxPosition = $playlist->tracks()->max('position') ?? 0;
            
            // Добавляем трек на следующую позицию
            $playlist->tracks()->attach($request->track_id, [
                'position' => $maxPosition + 1
            ]);
            
            return response()->json(['message' => 'Трек успешно добавлен в плейлист']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось добавить трек в плейлист'], 500);
        }
    }
    
    /**
     * Удалить трек из плейлиста
     */
    public function removeTrack(Request $request, $playlistId)
    {
        try {
            $request->validate([
                'track_id' => 'required|exists:tracks,id'
            ]);
            
            // Получаем пользователя из JWT токена
            $user = JWTAuth::parseToken()->authenticate();
            
            // Находим плейлист
            $playlist = $user->playlists()->findOrFail($playlistId);
            
            // Удаляем трек из плейлиста
            $playlist->tracks()->detach($request->track_id);
            
            return response()->json(['message' => 'Трек успешно удален из плейлиста']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось удалить трек из плейлиста'], 500);
        }
    }
}
