<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Track;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        if (!$query) {
            return response()->json([
                'tracks' => [],
                'artists' => []
            ]);
        }

        // Поиск треков по названию или артисту
        $tracks = Track::where('title', 'like', "%$query%")
            ->orWhere('artist', 'like', "%$query%")
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist,
                    'image' => $track->cover_path ? asset('storage/' . $track->cover_path) : null,
                    'file_path' => $track->file_path ?? null,
                ];
            });

        // Список уникальных артистов из найденных треков
        $artists = $tracks->pluck('artist')->unique()->map(function ($artist, $i) use ($tracks) {
            $track = $tracks->firstWhere('artist', $artist);
            return [
                'id' => $i + 1, // временный id
                'name' => $artist,
                'image' => $track ? $track['image'] : null,
            ];
        })->values();

        return response()->json([
            'tracks' => $tracks,
            'artists' => $artists
        ]);
    }
} 