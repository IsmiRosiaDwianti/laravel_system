<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceLog extends Model
{
    protected $fillable = [
        'service_id',
        'status',
        'response_code',
        'response_time',
        'message'
    ];

    public function service()
    {
        return $this->belongsTo(
            Service::class
        );
    }
}