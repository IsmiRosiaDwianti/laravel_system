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
        'message',
        'is_status_change',
        'previous_status',
        'action',
        'checked_at', // ✅ TAMBAHKAN INI
    ];

    protected $casts = [
        'response_time' => 'float',
        'is_status_change' => 'boolean',
        'checked_at' => 'datetime', // ✅ TAMBAHKAN INI
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan service
     */
    public function scopeServiceId($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Scope untuk filter perubahan status
     */
    public function scopeStatusChange($query)
    {
        return $query->where('is_status_change', true);
    }

    /**
     * Scope untuk filter rentang tanggal
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Cek apakah ini perubahan status
     */
    public function isStatusChange(): bool
    {
        return $this->is_status_change ?? false;
    }

    /**
     * Mendapatkan status sebelumnya
     */
    public function getPreviousStatusAttribute($value)
    {
        return $value ?? 'UNKNOWN';
    }

    /**
     * Mendapatkan label status dengan warna
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'UP' => ['label' => 'UP', 'class' => 'success'],
            'WARNING' => ['label' => 'WARNING', 'class' => 'warning'],
            'DOWN' => ['label' => 'DOWN', 'class' => 'danger'],
            'UNKNOWN' => ['label' => 'UNKNOWN', 'class' => 'secondary'],
        ];

        return $labels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
    }

    /**
     * Mendapatkan ikon status
     */
    public function getStatusIconAttribute()
    {
        $icons = [
            'UP' => '✅',
            'WARNING' => '⚠️',
            'DOWN' => '❌',
            'UNKNOWN' => '❓',
        ];

        return $icons[$this->status] ?? '❓';
    }

    /**
     * Format response time
     */
    public function getFormattedResponseTimeAttribute()
    {
        if ($this->response_time === null) {
            return '-';
        }
        return number_format($this->response_time, 3) . ' s';
    }

    /**
     * Cek apakah ada action/tindakan
     */
    public function hasAction(): bool
    {
        return !empty($this->action) && $this->action !== '-';
    }

    /**
     * Mendapatkan ringkasan log
     */
    public function getSummaryAttribute()
    {
        $statusIcon = $this->status_icon;
        $statusLabel = $this->status_label['label'];
        $time = $this->created_at->format('d/m/Y H:i:s');
        
        if ($this->is_status_change) {
            return "{$statusIcon} Status berubah: {$this->previous_status} → {$statusLabel} ({$time})";
        }
        
        return "{$statusIcon} {$statusLabel} - {$this->message} ({$time})";
    }
}