<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\Guest;
use App\Models\ScanOperation;
use App\Models\Station;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JsonException;

class QrScanService
{
    public function blockedReason(Guest $guest): ?string
    {
        if (! $guest->is_active) {
            return 'Mã QR không hoạt động.';
        }
        if ($guest->is_locked) {
            return 'Mã QR đã bị khóa.';
        }
        if (! $guest->canScan()) {
            return 'Mã QR đã đạt giới hạn quét.';
        }

        return null;
    }

    public function nextAction(Guest $guest): string
    {
        // A reset one-time pass still means check-in only.
        return $guest->scan_mode !== 'one_time' && $guest->isCheckedIn() ? 'checkout' : 'checkin';
    }

    public function confirmation(Guest $guest, ?Station $station): string
    {
        return Crypt::encryptString(json_encode([
            'request_id' => (string) Str::uuid(),
            'qr_code' => $guest->qr_code,
            'station' => $station?->scan_slug,
            'state' => $this->state($guest),
            'action' => $this->nextAction($guest),
            'expires_at' => now()->timestamp + config('welcome.confirmation_ttl_seconds'),
        ], JSON_THROW_ON_ERROR));
    }

    public function record(string $qrCode, string $confirmation, ?string $ip): ScanOperation
    {
        try {
            $intent = json_decode(Crypt::decryptString($confirmation), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException $e) {
            abort(422, 'Xác nhận không hợp lệ. Vui lòng mở lại mã QR.');
        }
        abort_unless(is_array($intent), 422);
        Validator::make($intent, [
            'request_id' => 'required|uuid',
            'qr_code' => 'required|string',
            'station' => 'nullable|string',
            'state' => 'required|string|size:64',
            'action' => 'required|in:checkin,checkout',
            'expires_at' => 'required|integer',
        ])->validate();
        abort_unless($intent['qr_code'] === $qrCode, 422, 'Xác nhận không thuộc mã QR này.');

        return DB::transaction(function () use ($qrCode, $intent, $ip) {
            $guest = Guest::where('qr_code', $qrCode)->lockForUpdate()->firstOrFail();

            // Replay succeeds even if this operation exhausted/locked the pass.
            $existing = ScanOperation::where('request_id', $intent['request_id'])
                ->where('guest_id', $guest->id)->first();
            if ($existing) {
                return $existing;
            }

            abort_if($intent['expires_at'] <= now()->timestamp, 419, 'Xác nhận đã hết hạn. Vui lòng mở lại mã QR.');
            if ($reason = $this->blockedReason($guest)) {
                abort(403, $reason);
            }

            // Never silently replace a requested invalid station with the global station.
            $station = isset($intent['station'])
                ? Station::where('scan_slug', $intent['station'])->where('is_active', true)->firstOrFail()
                : null;

            abort_unless(hash_equals($this->state($guest), $intent['state']), 409,
                'Trạng thái khách đã thay đổi. Vui lòng mở lại mã QR và xác nhận thao tác mới.');
            $action = $this->nextAction($guest);
            abort_unless($intent['action'] === $action, 409);

            $last = ScanOperation::where('guest_id', $guest->id)->latest('id')->first();
            abort_if($last && $last->created_at->gt(now()->subSeconds(config('welcome.scan_cooldown_seconds'))),
                429, 'Vừa ghi nhận lượt quét. Vui lòng chờ vài giây trước khi xác nhận lượt tiếp theo.');

            // Compare-and-swap also protects engines where SELECT FOR UPDATE is a no-op (SQLite).
            $claimed = Guest::whereKey($guest->id)->where('scan_version', $guest->scan_version)
                ->update(['scan_version' => DB::raw('scan_version + 1')]);
            abort_unless($claimed === 1, 409, 'Trạng thái khách đã thay đổi. Vui lòng mở lại mã QR.');

            if ($action === 'checkout') {
                $checkin = $guest->latestCheckin;
                $checkin->update(['checkout_at' => now()]);
            } else {
                $checkin = Checkin::create([
                    'guest_id' => $guest->id,
                    'station_id' => $station?->id,
                    'checkin_at' => now(),
                    'ip_address' => $ip,
                ]);
            }
            $guest->recordScan();

            return ScanOperation::create([
                'request_id' => $intent['request_id'],
                'guest_id' => $guest->id,
                'station_id' => $station?->id,
                'checkin_id' => $checkin->id,
                'action' => $action,
            ]);
        }, attempts: 5);
    }

    private function state(Guest $guest): string
    {
        $latest = $guest->latestCheckin;

        return hash('sha256', json_encode([
            (int) $guest->scan_version, (int) $guest->scan_count, $guest->scan_mode,
            (int) $guest->max_scan_count, $guest->is_active, $guest->is_locked,
            $latest?->id, $latest?->checkin_at?->toISOString(), $latest?->checkout_at?->toISOString(),
        ], JSON_THROW_ON_ERROR));
    }
}
