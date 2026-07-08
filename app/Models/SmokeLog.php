<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmokeLog extends Model
{
    protected $fillable = [
        'smoke_device_id',           // ✅ PASTIKAN smoke_device_id (bukan device_id)
        'smoke_value',
        'status',
        'message',
    ];

    protected $casts = [
        'smoke_value' => 'integer',
    ];

    // ✅ Relasi ke SmokeDevice
    public function device()
    {
        return $this->belongsTo(SmokeDevice::class, 'smoke_device_id'); // ✅ PASTIKAN device_id
    }

    // ... (method lainnya tetap sama)
}