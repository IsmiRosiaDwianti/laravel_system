<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmokeDevice extends Model
{
    protected $fillable = [

        'name',
        'location',
        'threshold',

        'smoke_value',
        'status',

        'device_status',
        'last_seen_at',

        'is_active',

        'last_smoke_value',
        'last_status',

        'last_status_notified'

    ];

    protected $casts = [

        'is_active' => 'boolean',

        'last_seen_at' => 'datetime'

    ];

    public function logs()
    {
        return $this->hasMany(
            SmokeLog::class
        );
    }

    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at
            ->gt(now()->subMinutes(5));
    }

    public function isDanger()
    {
        return $this->last_status === 'DANGER';
    }
}