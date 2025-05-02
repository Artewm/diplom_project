<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index()
    {
        /* 
        // Код закомментирован, так как методы и отношения,
        // используемые здесь, не определены в моделях
        $tracks = Auth::user()
            ->library()
            ->with(['artist', 'album'])
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'title' => $track->title,
                    'artist' => $track->artist->name,
                    'cover_url' => $track->album->cover_url,
                    'duration' => $track->duration,
                    'is_favorite' => $track->is_favorite,
                    'is_album' => false,
                    'is_artist' => false,
                ];
            });
        */
        
        // Временная реализация, возвращающая все треки
        $tracks = Track::all();
        return response()->json($tracks);
    }

    public function toggleFavorite(Track $track)
    {
        /* 
        // Код закомментирован, так как поле is_favorite не определено в модели Track
        $track->is_favorite = !$track->is_favorite;
        $track->save();
        */
        
        // Правильная реализация через модель Favorite
        $user = Auth::user();
        $favorite = $user->favorites()->where('track_id', $track->id)->first();
        
        if ($favorite) {
            $favorite->delete();
            $message = 'Track removed from favorites';
        } else {
            $user->favorites()->create(['track_id' => $track->id]);
            $message = 'Track added to favorites';
        }
        
        return response()->json(['success' => true, 'message' => $message]);
    }
} 