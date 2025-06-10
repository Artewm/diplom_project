<?php


namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use getID3;
use Tymon\JWTAuth\Facades\JWTAuth;

class TrackController extends Controller
{
    // Получить все треки
    public function index()
    {
        return Track::all();
    }

    // Создать новый трек
    public function store(Request $request)
    {
        try {
            \Log::info('DEBUG: Весь запрос', $request->all());
            \Log::info('DEBUG: Файл file', ['file' => $request->file('file')]);
            
            $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();

            // Проверяем наличие файла
            if (!$request->hasFile('file')) {
                \Log::error('Файл не найден в запросе');
                return response()->json(['error' => 'Файл не был загружен'], 400);
            }

            $file = $request->file('file');
            
            // Проверяем валидность файла
            if (!$file->isValid()) {
                \Log::error('Файл поврежден или не загружен полностью');
                return response()->json(['error' => 'Файл поврежден или не загружен полностью'], 400);
            }

            // Валидация остальных полей
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'artist' => 'required|string|max:255',
                'file' => 'required|file|mimes:mp3,wav,ogg|max:20480',
            ]);

            if ($validator->fails()) {
                \Log::error('Ошибка валидации', $validator->errors()->toArray());
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Подготовка пути для сохранения
            $uploadPath = storage_path('app/public/tracks');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Генерация безопасного имени файла
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());
            $fileFullPath = storage_path('app/public/tracks/' . $fileName);
            if (!$file->move(storage_path('app/public/tracks'), $fileName)) {
                \Log::error('Не удалось сохранить файл (move)');
                return response()->json(['error' => 'Не удалось сохранить файл'], 500);
            }
            $filePath = 'tracks/' . $fileName;

            \Log::info('Файл успешно сохранен', ['path' => $filePath]);

            // === Расчет длительности трека ===
            require_once base_path('vendor/james-heinrich/getid3/getid3/getid3.php');
            $getID3 = new \getID3();
            $audio = $getID3->analyze($fileFullPath);
            $duration = isset($audio['playtime_seconds']) ? $audio['playtime_seconds'] : 0;
            $durationFormatted = '0:00';
            if ($duration > 0) {
                $minutes = floor($duration / 60);
                $seconds = str_pad(floor($duration % 60), 2, '0', STR_PAD_LEFT);
                $durationFormatted = "$minutes:$seconds";
            }
            // === конец расчета ===

            // === Обработка обложки ===
            $coverPath = 'images/baseMusic.png'; // дефолт
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                if ($image->isValid()) {
                    $imageName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $image->getClientOriginalName());
                    if (!$image->move(storage_path('app/public/covers'), $imageName)) {
                        \Log::error('Не удалось сохранить обложку (move)');
                    } else {
                        $coverPath = 'covers/' . $imageName;
                    }
                }
            }
            // === конец обработки обложки ===

            // Создание записи в БД
            $track = Track::create([
                'title' => $request->title,
                'artist' => $request->artist,
                'genre' => $request->genre ?? 'Неизвестный',
                'duration' => $durationFormatted,
                'file_path' => $filePath,
                'cover_path' => $coverPath,
                'user_id' => $user->id
            ]);

            \Log::info('Трек успешно создан', ['track_id' => $track->id]);
            return response()->json($track, 201);

        } catch (\Exception $e) {
            \Log::error('Ошибка при загрузке трека: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при загрузке трека: ' . $e->getMessage()], 500);
        }
    }

    // Получить один трек по ID
    public function show($id)
    {
        return Track::findOrFail($id);
    }

    // Обновить трек
    public function update(Request $request, $id)
    {
        $track = Track::findOrFail($id);
        $track->update($request->all());
        return $track;
    }

    // Удалить трек
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $track = Track::findOrFail($id);
        // Любой пользователь может удалять только свои треки
        if (isset($track->user_id) && $track->user_id !== $user->id) {
            return response()->json(['error' => 'Нет прав на удаление этого трека'], 403);
        }
        $track->delete();
        return response()->json(null, 204);
    }
}
