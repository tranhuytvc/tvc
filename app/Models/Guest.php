<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    protected $fillable = ['name', 'email', 'media_type', 'media_path', 'qr_token', 'qr_code_path', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($guest) {
            if (empty($guest->qr_token)) {
                $guest->qr_token = Str::random(16);
            }
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
}
