<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Checkin;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function show(string $token)
    {
        $guest = Guest::where('qr_token', $token)->where('is_active', true)->firstOrFail();

        $latest = $guest->latestCheckin;
        if ($latest && $latest->checkin_at && !$latest->checkout_at) {
            $latest->update(['checkout_at' => now()]);
            $action = 'checkout';
        } else {
            Checkin::create([
                'guest_id' => $guest->id,
                'checkin_at' => now(),
                'ip_address' => request()->ip(),
            ]);
            $action = 'checkin';
        }

        return view('welcome', compact('guest', 'action'));
    }

    public function scan()
    {
        return view('scan');
    }

    public function display()
    {
        return view('display');
    }
}
