<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Station;
use App\Models\Checkin;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function show(string $qrCode, Request $request)
    {
        $guest = Guest::where('qr_code', $qrCode)->firstOrFail();

        if (!$guest->is_active) {
            return $this->blocked($guest, 'Mã QR không hoạt động.', null, Station::defaultSettings());
        }

        $stationSlug = $request->get('station');
        $station = $stationSlug
            ? Station::where('scan_slug', $stationSlug)->where('is_active', true)->first()
            : null;
        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();

        if ($guest->is_locked) {
            return $this->blocked($guest, 'Mã QR đã bị khóa.', $station, $settings);
        }

        if (!$guest->canScan()) {
            return $this->blocked($guest, 'Mã QR đã đạt giới hạn quét.', $station, $settings);
        }

        // Determine action: checkin or checkout
        $latest = $guest->latestCheckin;
        if ($latest && $latest->checkin_at && !$latest->checkout_at) {
            // Currently checked in → checkout
            $latest->update(['checkout_at' => now()]);
            $action = 'checkout';
        } else {
            // Not checked in → checkin
            Checkin::create([
                'guest_id'   => $guest->id,
                'station_id' => $station?->id,
                'checkin_at' => now(),
                'ip_address' => request()->ip(),
            ]);
            $action = 'checkin';
        }

        $guest->recordScan();

        return view('welcome', compact('guest', 'action', 'station', 'settings'));
    }

    public function scan(?string $slug = null)
    {
        $station = null;
        if ($slug) {
            $station = Station::where('scan_slug', $slug)->where('is_active', true)->firstOrFail();
        }
        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
        return view('scan', compact('station', 'settings'));
    }

    public function display(?string $slug = null)
    {
        $station = null;
        if ($slug) {
            $station = Station::where('display_slug', $slug)->where('is_active', true)->firstOrFail();
        }
        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
        return view('display', compact('station', 'settings'));
    }

    private function blocked(Guest $guest, string $reason, ?Station $station, array $settings)
    {
        return view('blocked', compact('guest', 'reason', 'station', 'settings'));
    }
}
