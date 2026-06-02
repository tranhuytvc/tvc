<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    protected $fillable = [
        'name', 'email', 'media_type', 'media_path', 'import_media_name', 'qr_code_path',
        'is_active', 'scan_mode', 'max_scan_count', 'scan_count', 'is_locked',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    // Scan mode labels
    public const SCAN_MODES = [
        'unlimited'        => 'Không giới hạn',
        'one_time'         => 'Một lần (chỉ check-in)',
        'checkin_checkout' => 'Vào & Ra (2 lần)',
        'max_scans'        => 'Tối đa N lần',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($guest) {
            $guest->qr_code = (string) Str::uuid();
        });
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function latestCheckin()
    {
        return $this->hasOne(Checkin::class)->latestOfMany();
    }

    public function isCheckedIn(): bool
    {
        $latest = $this->latestCheckin;
        return $latest && $latest->checkin_at && !$latest->checkout_at;
    }

    // Can this QR be scanned right now?
    public function canScan(): bool
    {
        if (!$this->is_active) return false;
        if ($this->is_locked) return false;

        return match ($this->scan_mode) {
            'unlimited'        => true,
            'one_time'         => $this->scan_count < 1,
            'checkin_checkout' => $this->scan_count < 2,
            'max_scans'        => $this->scan_count < $this->max_scan_count,
            default            => true,
        };
    }

    // Record a scan and auto-lock if limit reached
    public function recordScan(): void
    {
        $this->increment('scan_count');

        $shouldLock = match ($this->scan_mode) {
            'one_time'         => $this->scan_count >= 1,
            'checkin_checkout' => $this->scan_count >= 2,
            'max_scans'        => $this->scan_count >= $this->max_scan_count,
            default            => false,
        };

        if ($shouldLock) {
            $this->update(['is_locked' => true]);
        }
    }

    public function getScanModeLabel(): string
    {
        return self::SCAN_MODES[$this->scan_mode] ?? $this->scan_mode;
    }

    public function getScanStatusBadge(): string
    {
        if ($this->is_locked) return 'locked';
        if (!$this->is_active) return 'inactive';
        if (!$this->canScan()) return 'exhausted';
        return 'available';
    }

    // Remaining scans allowed (null = unlimited)
    public function remainingScans(): ?int
    {
        return match ($this->scan_mode) {
            'unlimited'        => null,
            'one_time'         => max(0, 1 - $this->scan_count),
            'checkin_checkout' => max(0, 2 - $this->scan_count),
            'max_scans'        => max(0, $this->max_scan_count - $this->scan_count),
            default            => null,
        };
    }
}
