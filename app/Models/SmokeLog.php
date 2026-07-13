<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SmokeLog extends Model
{
    /**
     * ============================================================
     *  📋 FILLABLE - Kolom yang boleh diisi
     * ============================================================
     */
    protected $fillable = [
        'smoke_device_id',    // ID device (foreign key ke SmokeDevice)
        'smoke_value',        // Nilai PPM
        'status',            // Status: NORMAL, WARNING, DANGER
        'message',           // Pesan keterangan
    ];

    /**
     * ============================================================
     *  🎯 CASTS - Konversi tipe data
     * ============================================================
     */
    protected $casts = [
        'smoke_value' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ============================================================
     *  🔗 RELASI KE SmokeDevice
     * ============================================================
     */
    public function device()
    {
        return $this->belongsTo(SmokeDevice::class, 'smoke_device_id');
    }

    /**
     * ============================================================
     *  📊 SCOPE: Ambil hanya log perubahan status
     * ============================================================
     */
    public function scopeStatusChanges($query)
    {
        return $query->orderBy('created_at', 'asc')
            ->get()
            ->filter(function ($item, $key) use ($query) {
                if ($key === 0) return true;
                
                $previous = $query->getModel()
                    ->where('smoke_device_id', $item->smoke_device_id)
                    ->where('created_at', '<', $item->created_at)
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                return $previous && $item->status !== $previous->status;
            });
    }

    /**
     * ============================================================
     *  📊 SCOPE: Ambil log terbaru untuk device tertentu
     * ============================================================
     */
    public function scopeLatestForDevice($query, $deviceId)
    {
        return $query->where('smoke_device_id', $deviceId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * ============================================================
     *  📊 SCOPE: Filter berdasarkan status
     * ============================================================
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * ============================================================
     *  📊 SCOPE: Filter berdasarkan rentang tanggal
     * ============================================================
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * ============================================================
     *  📊 ACCESSOR: Status dengan ikon
     * ============================================================
     */
    public function getStatusWithIconAttribute()
    {
        $icons = [
            'DANGER' => '🔴 DANGER',
            'WARNING' => '🟡 WARNING',
            'NORMAL' => '🟢 NORMAL',
        ];
        return $icons[$this->status] ?? '🟢 NORMAL';
    }

    /**
     * ============================================================
     *  📊 ACCESSOR: Status class (untuk CSS)
     * ============================================================
     */
    public function getStatusClassAttribute()
    {
        $classes = [
            'DANGER' => 'danger',
            'WARNING' => 'warning',
            'NORMAL' => 'normal',
        ];
        return $classes[$this->status] ?? 'normal';
    }

    /**
     * ============================================================
     *  📊 ACCESSOR: Pesan singkat berdasarkan status
     * ============================================================
     */
    public function getShortMessageAttribute()
    {
        if ($this->message) {
            return $this->message;
        }

        return match($this->status) {
            'DANGER' => '🔥 Asap tinggi! Segera periksa!',
            'WARNING' => '⚠️ Asap terdeteksi! Waspada!',
            default => '✅ Kondisi aman',
        };
    }

    /**
     * ============================================================
     *  📊 ACCESSOR: Durasi status (sejak log ini sampai log berikutnya)
     * ============================================================
     */
    public function getDurationAttribute()
    {
        if (!$this->id) return null;
        
        $next = self::where('smoke_device_id', $this->smoke_device_id)
            ->where('id', '>', $this->id)
            ->orderBy('id', 'asc')
            ->first();
            
        if ($next) {
            $diffInMinutes = Carbon::parse($this->created_at)->diffInMinutes(Carbon::parse($next->created_at));
            
            if ($diffInMinutes < 1) {
                $diffInSeconds = Carbon::parse($this->created_at)->diffInSeconds(Carbon::parse($next->created_at));
                return $diffInSeconds . ' detik';
            } elseif ($diffInMinutes < 60) {
                return $diffInMinutes . ' menit';
            } else {
                $hours = floor($diffInMinutes / 60);
                $minutes = $diffInMinutes % 60;
                return $hours . ' jam ' . $minutes . ' menit';
            }
        }
        
        return 'Berlangsung';
    }

    /**
     * ============================================================
     *  📊 ACCESSOR: Waktu format Indonesia
     * ============================================================
     */
    public function getCreatedAtIndonesiaAttribute()
    {
        return $this->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s');
    }

    /**
     * ============================================================
     *  📊 ACCESSOR: Waktu format untuk chart
     * ============================================================
     */
    public function getTimeForChartAttribute()
    {
        return $this->created_at->setTimezone('Asia/Jakarta')->format('H:i:s');
    }

    /**
     * ============================================================
     *  📊 METHOD: Cek apakah log ini adalah perubahan status
     * ============================================================
     */
    public function isStatusChanged()
    {
        $previous = self::where('smoke_device_id', $this->smoke_device_id)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if (!$previous) return true; // Log pertama
        
        return $this->status !== $previous->status;
    }

    /**
     * ============================================================
     *  📊 METHOD: Dapatkan log sebelumnya
     * ============================================================
     */
    public function getPreviousLog()
    {
        return self::where('smoke_device_id', $this->smoke_device_id)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * ============================================================
     *  📊 METHOD: Dapatkan log berikutnya
     * ============================================================
     */
    public function getNextLog()
    {
        return self::where('smoke_device_id', $this->smoke_device_id)
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * ============================================================
     *  🎯 BOOT: Event model
     * ============================================================
     */
    protected static function boot()
    {
        parent::boot();

        // Event ketika log dibuat
        static::created(function ($log) {
            \Illuminate\Support\Facades\Log::info("📝 Log smoke baru: {$log->status} - {$log->smoke_value} ppm");
        });

        // Event ketika log diupdate
        static::updated(function ($log) {
            \Illuminate\Support\Facades\Log::info("📝 Log smoke diupdate: {$log->status} - {$log->smoke_value} ppm");
        });
    }

    /**
     * ============================================================
     *  🔍 SEARCH SCOPES (untuk fitur pencarian)
     * ============================================================
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function($q) use ($keyword) {
                $q->where('status', 'LIKE', "%{$keyword}%")
                  ->orWhere('smoke_value', 'LIKE', "%{$keyword}%")
                  ->orWhere('message', 'LIKE', "%{$keyword}%")
                  ->orWhereHas('device', function($deviceQuery) use ($keyword) {
                      $deviceQuery->where('name', 'LIKE', "%{$keyword}%")
                                  ->orWhere('location', 'LIKE', "%{$keyword}%");
                  });
            });
        }
        return $query;
    }

    /**
     * ============================================================
     *  📊 STATISTIK: Hitung rata-rata PPM per status
     * ============================================================
     */
    public static function getAverageByStatus($deviceId = null)
    {
        $query = self::query();
        
        if ($deviceId) {
            $query->where('smoke_device_id', $deviceId);
        }
        
        return $query->select('status', \DB::raw('AVG(smoke_value) as avg_ppm'))
            ->groupBy('status')
            ->get()
            ->pluck('avg_ppm', 'status')
            ->toArray();
    }

    /**
     * ============================================================
     *  📊 STATISTIK: Hitung total log per status
     * ============================================================
     */
    public static function getCountByStatus($deviceId = null)
    {
        $query = self::query();
        
        if ($deviceId) {
            $query->where('smoke_device_id', $deviceId);
        }
        
        return $query->select('status', \DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
    }
}