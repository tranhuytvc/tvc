<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = ['name', 'email', 'media_type', 'media_path', 'qr_code_path', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

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
