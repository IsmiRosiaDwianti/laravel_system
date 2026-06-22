<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmokeLog extends Model
{
    protected $fillable = [
        'smoke_device_id',
        'smoke_value',
        'status'
    ];

    public function device()
    {
        return $this->belongsTo(SmokeDevice::class, 'smoke_device_id');
    }
}