<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index()
    {
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

        return response()->json($tracks);
    }

    public function toggleFavorite(Track $track)
    {
        $track->is_favorite = !$track->is_favorite;
        $track->save();

        return response()->json(['success' => true]);
    }
} 