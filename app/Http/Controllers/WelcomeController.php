<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Station;
use App\Models\Checkin;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    // Show welcome page after QR scan - optionally associated with a station
    public function show(Guest $guest, Request $request)
    {
        if (!$guest->is_active) {
            abort(404);
        }

        $stationSlug = $request->get('station');
        $station = $stationSlug ? Station::where('scan_slug', $stationSlug)->where('is_active', true)->first() : null;

        $latest = $guest->latestCheckin;
        if ($latest && $latest->checkin_at && !$latest->checkout_at) {
            $latest->update(['checkout_at' => now()]);
            $action = 'checkout';
        } else {
            Checkin::create([
                'guest_id' => $guest->id,
                'station_id' => $station?->id,
                'checkin_at' => now(),
                'ip_address' => request()->ip(),
            ]);
            $action = 'checkin';
        }

        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();

        return view('welcome', compact('guest', 'action', 'station', 'settings'));
    }

    // Scan page - with or without station
    public function scan(?string $slug = null)
    {
        $station = null;
        if ($slug) {
            $station = Station::where('scan_slug', $slug)->where('is_active', true)->firstOrFail();
        }
        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
        return view('scan', compact('station', 'settings'));
    }

    // Display page - with or without station
    public function display(?string $slug = null)
    {
        $station = null;
        if ($slug) {
            $station = Station::where('display_slug', $slug)->where('is_active', true)->firstOrFail();
        }
        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
        return view('display', compact('station', 'settings'));
    }
}
