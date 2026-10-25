<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\ScanOperation;
use App\Models\Station;
use App\Services\QrScanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class WelcomeController extends Controller
{
    public function show(string $qrCode, Request $request, QrScanService $scans)
    {
        $guest = Guest::where('qr_code', $qrCode)->firstOrFail();
        $data = $request->validate([
            'receipt' => 'nullable|uuid',
            'station' => 'nullable|string|max:255',
        ]);

        if (! empty($data['receipt'])) {
            $receipt = ScanOperation::where('request_id', $data['receipt'])
                ->where('guest_id', $guest->id)->firstOrFail();
            $station = $receipt->station;
            $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
            $action = $receipt->action;

            return response()->view('welcome', compact('guest', 'action', 'station', 'settings', 'receipt'))
                ->header('Cache-Control', 'no-store, private');
        }

        $station = ! empty($data['station'])
            ? Station::where('scan_slug', $data['station'])->where('is_active', true)->firstOrFail()
            : null;
        $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
        if ($reason = $scans->blockedReason($guest)) {
            return $this->blocked($guest, $reason, $station, $settings);
        }

        $action = $scans->nextAction($guest);
        $confirmation = $scans->confirmation($guest, $station);

        return response()->view('welcome', compact('guest', 'action', 'station', 'settings', 'confirmation'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function store(string $qrCode, Request $request, QrScanService $scans)
    {
        $data = $request->validate(['confirmation' => 'required|string|max:4096']);
        try {
            $receipt = $scans->record($qrCode, $data['confirmation'], $request->ip());
        } catch (HttpExceptionInterface $e) {
            if (! in_array($e->getStatusCode(), [403, 409, 419, 422, 429])) {
                throw $e;
            }
            $guest = Guest::where('qr_code', $qrCode)->first();
            if (! $guest) {
                throw $e;
            }
            // Query station affects error presentation only; the signed intent controls writes.
            $slug = $request->query('station');
            $station = is_string($slug)
                ? Station::where('scan_slug', $slug)->where('is_active', true)->first()
                : null;
            $settings = $station ? $station->mergedSettings() : Station::defaultSettings();
            $reason = $e->getMessage();
            $retryUrl = route('welcome', ['qrCode' => $qrCode, 'station' => $station?->scan_slug]);
            $response = response()->view('blocked', compact('guest', 'reason', 'station', 'settings', 'retryUrl'), $e->getStatusCode())
                ->header('Cache-Control', 'no-store, private');
            if ($e->getStatusCode() === 429) {
                $response->header('Retry-After', (string) config('welcome.scan_cooldown_seconds'));
            }

            return $response;
        }

        // POST / Redirect / GET: refreshing the result can never submit a new scan.
        return redirect()->route('welcome', [
            'qrCode' => $qrCode,
            'receipt' => $receipt->request_id,
        ], 303);
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
        return response()->view('blocked', compact('guest', 'reason', 'station', 'settings'))
            ->header('Cache-Control', 'no-store, private');
    }
}
