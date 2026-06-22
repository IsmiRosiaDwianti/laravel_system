<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'target',
        'type',
        'last_status',
        'last_code',
        'last_response_time',
        'last_message'
    ];

    public function logs()
    {
        return $this->hasMany(ServiceLog::class);
    }
}