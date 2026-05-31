<?php

use App\Models\Checkin;
use App\Models\Station;
use Illuminate\Support\Facades\Route;

// Global latest checkin (no station filter)
Route::get('/latest-checkin', function (\Illuminate\Http\Request $request) {
    $after = $request->get('after', 0);
    $checkin = Checkin::with('guest')
        ->where('id', '>', $after)
        ->orderByDesc('id')
        ->first();

    if (!$checkin) return response()->json(null);

    return response()->json([
        'id' => $checkin->id,
        'name' => $checkin->guest->name ?? '',
        'media_path' => $checkin->guest->media_path ?? null,
        'media_type' => $checkin->guest->media_type ?? 'image',
        'action' => $checkin->checkout_at ? 'checkout' : 'checkin',
        'time' => $checkin->checkin_at->format('H:i:s'),
    ]);
});

// Station-specific latest checkin
Route::get('/station/{slug}/latest-checkin', function (\Illuminate\Http\Request $request, string $slug) {
    $station = Station::where('display_slug', $slug)->first();
    if (!$station) return response()->json(null);

    $after = $request->get('after', 0);
    $checkin = Checkin::with('guest')
        ->where('station_id', $station->id)
        ->where('id', '>', $after)
        ->orderByDesc('id')
        ->first();

    if (!$checkin) return response()->json(null);

    return response()->json([
        'id' => $checkin->id,
        'name' => $checkin->guest->name ?? '',
        'media_path' => $checkin->guest->media_path ?? null,
        'media_type' => $checkin->guest->media_type ?? 'image',
        'action' => $checkin->checkout_at ? 'checkout' : 'checkin',
        'time' => $checkin->checkin_at->format('H:i:s'),
    ]);
});
