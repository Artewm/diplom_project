<?php


namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use getID3;

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
                'file' => 'required|file|mimes:mp3,wav,ogg|max:10240',
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
            $filePath = $file->storeAs('public/tracks', $fileName);

            if (!$filePath) {
                \Log::error('Не удалось сохранить файл');
                return response()->json(['error' => 'Не удалось сохранить файл'], 500);
            }

            \Log::info('Файл успешно сохранен', ['path' => $filePath]);

            // === Расчет длительности трека ===
            require_once base_path('vendor/james-heinrich/getid3/getid3/getid3.php');
            $getID3 = new \getID3();
            $audio = $getID3->analyze(storage_path('app/public/' . $fileName));
            $duration = isset($audio['playtime_seconds']) ? $audio['playtime_seconds'] : 0;
            $durationFormatted = '0:00';
            if ($duration > 0) {
                $minutes = floor($duration / 60);
                $seconds = str_pad(floor($duration % 60), 2, '0', STR_PAD_LEFT);
                $durationFormatted = "$minutes:$seconds";
            }
            // === конец расчета ===

            // Создание записи в БД
            $track = Track::create([
                'title' => $request->title,
                'artist' => $request->artist,
                'genre' => $request->genre ?? 'Неизвестный',
                'duration' => $durationFormatted,
                'file_path' => str_replace('public/', '', $filePath)
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
        Track::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
