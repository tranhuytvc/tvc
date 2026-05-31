<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Station;
use App\Models\Checkin;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $stationId = $request->get('station_id');

        $stations = Station::orderBy('name')->get();

        $checkinQuery = Checkin::whereDate('checkin_at', $date);
        if ($stationId) {
            $checkinQuery->where('station_id', $stationId);
        }

        $guests = Guest::with(['checkins' => function ($q) use ($date, $stationId) {
            $q->whereDate('checkin_at', $date);
            if ($stationId) $q->where('station_id', $stationId);
            $q->orderBy('checkin_at');
        }])->get();

        $totalGuests = Guest::where('is_active', true)->count();
        $checkedInToday = (clone $checkinQuery)->distinct('guest_id')->count('guest_id');
        $checkedOutToday = (clone $checkinQuery)->whereNotNull('checkout_at')->distinct('guest_id')->count('guest_id');
        $stillInside = max(0, $checkedInToday - $checkedOutToday);

        $recentCheckins = Checkin::with(['guest', 'station'])
            ->whereDate('checkin_at', $date)
            ->when($stationId, fn($q) => $q->where('station_id', $stationId))
            ->orderByDesc('checkin_at')
            ->take(50)->get();

        return view('stats', compact(
            'guests', 'date', 'totalGuests', 'stations', 'stationId',
            'checkedInToday', 'checkedOutToday', 'stillInside', 'recentCheckins'
        ));
    }
}
