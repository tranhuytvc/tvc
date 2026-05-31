<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Checkin;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $guests = Guest::with(['checkins' => function ($q) use ($date) {
            $q->whereDate('checkin_at', $date)->orderBy('checkin_at');
        }])->get();

        $totalGuests = Guest::where('is_active', true)->count();
        $checkedInToday = Checkin::whereDate('checkin_at', $date)->distinct('guest_id')->count('guest_id');
        $checkedOutToday = Checkin::whereDate('checkin_at', $date)->whereNotNull('checkout_at')->distinct('guest_id')->count('guest_id');
        $stillInside = $checkedInToday - $checkedOutToday;

        $recentCheckins = Checkin::with('guest')
            ->whereDate('checkin_at', $date)
            ->orderByDesc('checkin_at')
            ->take(50)
            ->get();

        return view('stats', compact(
            'guests', 'date', 'totalGuests',
            'checkedInToday', 'checkedOutToday',
            'stillInside', 'recentCheckins'
        ));
    }
}
