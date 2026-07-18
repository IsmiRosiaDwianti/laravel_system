<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'target',
        'type',
        'last_status',
        'last_code',
        'last_response_time',
        'last_message',
        'last_check_at',
        'last_wa_sent_at',
        'last_wa_status',
        // 🔥 DIPAKAI UNTUK INTERVAL (MANUAL & SCHEDULE)
        'wa_interval_minutes',
        // 🔥 FIELD UNTUK TRACKING INTERVAL
        'last_interval_checked_at',
        'last_interval_status',
        'interval_wa_sent_in_this_cycle',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_check_at' => 'datetime',
        'last_wa_sent_at' => 'datetime',
        'last_response_time' => 'float',
        // 🔥 DIPAKAI UNTUK INTERVAL
        'wa_interval_minutes' => 'integer',
        'last_interval_checked_at' => 'datetime',
        'last_interval_status' => 'string',
        'interval_wa_sent_in_this_cycle' => 'boolean',
    ];

    /**
     * Get the logs for the service.
     */
    public function logs()
    {
        return $this->hasMany(ServiceLog::class);
    }

    // ================================================================
    // 🔥 LOGIKA INTERVAL (DIPERTAHANKAN UNTUK KOMPATIBILITAS)
    // ================================================================

    /**
     * 🔥 CEK APAKAH SUDAH MELEWATI INTERVAL
     */
    public function isIntervalReached(): bool
    {
        $interval = $this->wa_interval_minutes ?? 0;
        
        if ($interval <= 0) {
            return false;
        }

        if (empty($this->last_interval_checked_at)) {
            return true;
        }

        $minutesSinceLastCheck = $this->last_interval_checked_at->diffInMinutes(now());
        
        return $minutesSinceLastCheck >= $interval;
    }

    /**
     * 🔥 MULAI INTERVAL BARU
     */
    public function startNewInterval(string $currentStatus): void
    {
        $this->update([
            'last_interval_checked_at' => now(),
            'last_interval_status' => $currentStatus,
            'interval_wa_sent_in_this_cycle' => false,
        ]);
    }

    /**
     * 🔥 TANDAI WA SUDAH TERKIRIM DI INTERVAL INI
     */
    public function markWaSentInThisCycle(): void
    {
        $this->update([
            'interval_wa_sent_in_this_cycle' => true,
        ]);
    }

    /**
     * 🔥 CEK APAKAH PERLU KIRIM WA (LOGIKA PER-SERVICE)
     */
    public function shouldSendWaByInterval(string $currentStatus): bool
    {
        $interval = $this->wa_interval_minutes ?? 0;
        
        if ($interval <= 0) {
            return false;
        }

        if ($currentStatus === 'UP') {
            if (empty($this->last_interval_checked_at)) {
                return true;
            }
            return false;
        }

        if (empty($this->last_interval_checked_at)) {
            return true;
        }

        if ($this->isIntervalReached() && !$this->interval_wa_sent_in_this_cycle) {
            return true;
        }

        if ($this->last_interval_status !== $currentStatus && 
            !$this->interval_wa_sent_in_this_cycle) {
            return true;
        }

        return false;
    }

    /**
     * 🔥 UPDATE WAKTU TERAKHIR KIRIM WA
     */
    public function updateLastWaSent($status)
    {
        $this->update([
            'last_wa_sent_at' => now(),
            'last_wa_status' => $status,
        ]);
    }

    // ================================================================
    // METHOD EXISTING (TIDAK BERUBAH)
    // ================================================================

    public function getUptime($days = 30)
    {
        $logs = $this->logs()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $logs->count();
        
        if ($total === 0) {
            if ($this->last_status === 'UP') return 100.00;
            elseif ($this->last_status === 'WARNING') return 70.00;
            elseif ($this->last_status === 'DOWN') return 0.00;
            return 0.00;
        }

        $totalWeight = 0;
        foreach ($logs as $log) {
            if ($log->status === 'UP') {
                $totalWeight += 100;
            } elseif ($log->status === 'WARNING') {
                $totalWeight += 70;
            } elseif ($log->status === 'DOWN') {
                $totalWeight += 0;
            }
        }
        
        $uptime = round($totalWeight / $total, 2);
        return max(0, min(100, $uptime));
    }

    public function getStatusInfo()
    {
        $status = $this->last_status ?? 'UNKNOWN';
        
        $statusMap = [
            'UP' => [
                'label' => 'UP',
                'class' => 'up',
                'color' => '#059669',
                'icon' => '✅'
            ],
            'DOWN' => [
                'label' => 'DOWN',
                'class' => 'down',
                'color' => '#dc2626',
                'icon' => '❌'
            ],
            'WARNING' => [
                'label' => 'WARNING',
                'class' => 'warning',
                'color' => '#d97706',
                'icon' => '⚠️'
            ],
            'UNKNOWN' => [
                'label' => 'UNKNOWN',
                'class' => 'unknown',
                'color' => '#94a3b8',
                'icon' => '❓'
            ]
        ];

        return $statusMap[$status] ?? $statusMap['UNKNOWN'];
    }

    public function isDown()
    {
        return $this->last_status === 'DOWN';
    }

    public function isUp()
    {
        return $this->last_status === 'UP';
    }

    public function isWarning()
    {
        return $this->last_status === 'WARNING';
    }

    public function getResponseTimeHuman()
    {
        if ($this->last_response_time === null) {
            return '-';
        }
        
        if ($this->last_response_time < 1) {
            return number_format($this->last_response_time * 1000, 0) . ' ms';
        }
        
        return number_format($this->last_response_time, 2) . ' s';
    }

    public function getLastCheckAtHuman()
    {
        if (!$this->last_check_at) {
            return '-';
        }
        
        return $this->last_check_at->setTimezone('Asia/Jakarta')->format('H:i:s');
    }

    public function getLastWaSentHuman()
    {
        if (!$this->last_wa_sent_at) {
            return 'Belum pernah';
        }
        
        return $this->last_wa_sent_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s');
    }

    public function getTimeSinceLastWa()
    {
        if (!$this->last_wa_sent_at) {
            return '-';
        }
        
        return $this->last_wa_sent_at->diffForHumans();
    }

    // ================================================================
    // 🔥 SCOPES
    // ================================================================

    public function scopeStatus($query, $status)
    {
        return $query->where('last_status', $status);
    }

    public function scopeUp($query)
    {
        return $query->where('last_status', 'UP');
    }

    public function scopeDown($query)
    {
        return $query->where('last_status', 'DOWN');
    }

    public function scopeWarning($query)
    {
        return $query->where('last_status', 'WARNING');
    }

    public function scopeWaInterval($query, $minutes)
    {
        return $query->where('wa_interval_minutes', $minutes);
    }

    public function scopeNeverSentWa($query)
    {
        return $query->whereNull('last_wa_sent_at');
    }

    public function scopeReadyForWaReminder($query)
    {
        return $query->where('wa_interval_minutes', '>', 0)
            ->where(function ($q) {
                $q->whereNull('last_wa_sent_at')
                    ->orWhereRaw('TIMESTAMPDIFF(MINUTE, last_wa_sent_at, NOW()) >= wa_interval_minutes');
            });
    }

    public static function getStatistics()
    {
        $total = self::count();
        $up = self::up()->count();
        $down = self::down()->count();
        $warning = self::warning()->count();
        $unknown = $total - ($up + $down + $warning);

        return [
            'total' => $total,
            'up' => $up,
            'down' => $down,
            'warning' => $warning,
            'unknown' => $unknown,
            'uptime_percentage' => $total > 0 ? round(($up / $total) * 100, 2) : 0,
        ];
    }
}