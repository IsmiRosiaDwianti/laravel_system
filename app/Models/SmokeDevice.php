<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmokeDevice extends Model
{
    protected $fillable = [
        'name',
        'location',
        'threshold',
        'is_active'
    ];

    public function logs()
    {
        return $this->hasMany(SmokeLog::class);
    }
}