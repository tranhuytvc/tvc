<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Station extends Model
{
    protected $fillable = ['name', 'scan_slug', 'display_slug', 'settings', 'is_active'];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($station) {
            if (empty($station->scan_slug)) {
                $station->scan_slug = Str::slug($station->name) . '-' . Str::random(6);
            }
            if (empty($station->display_slug)) {
                $station->display_slug = 'display-' . Str::slug($station->name) . '-' . Str::random(6);
            }
        });
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function mergedSettings(): array
    {
        return array_merge(self::defaultSettings(), $this->settings ?? []);
    }

    public static function defaultSettings(): array
    {
        return [
            'event_name' => 'Sự kiện',
            'logo_path' => null,
            'bg_type' => 'gradient',
            'bg_color_from' => '#0f0c29',
            'bg_color_to' => '#302b63',
            'bg_image' => null,
            'bg_video' => null,
            'accent_color' => '#667eea',
            'font_color' => '#ffffff',
            'welcome_title_checkin' => 'Xin chào,',
            'welcome_title_checkout' => 'Tạm biệt,',
            'welcome_msg_checkin' => 'Chào mừng bạn đã đến. Chúc bạn có một buổi tuyệt vời!',
            'welcome_msg_checkout' => 'Cảm ơn bạn đã tham dự. Hẹn gặp lại!',
            'scan_title' => 'Quét mã QR Check-in',
            'scan_subtitle' => 'Đưa mã QR vào khung hình để check-in / check-out',
            'display_orientation' => 'landscape',
            'countdown_seconds' => 10,
            'show_clock' => true,
            'idle_text' => 'Quét mã QR để check-in',
        ];
    }
}
