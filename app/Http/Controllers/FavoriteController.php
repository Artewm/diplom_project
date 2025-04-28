<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Track;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Track $track)
    {
        $favorite = auth()->user()->favorites()->where('track_id', $track->id)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        }

        auth()->user()->favorites()->create([
            'track_id' => $track->id
        ]);

        return response()->json(['status' => 'added']);
    }

    public function index()
    {
        $favorites = auth()->user()->favorites()->with('track')->get();
        return view('favorites.index', compact('favorites'));
    }
} 