<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanOperation extends Model
{
    protected $fillable = ['request_id', 'guest_id', 'station_id', 'checkin_id', 'action'];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
