<?php


namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

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
        $track = Track::create($request->all());
        return response()->json($track, 201);
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
