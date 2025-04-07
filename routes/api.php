<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;

Route::apiResource('tracks', TrackController::class);
