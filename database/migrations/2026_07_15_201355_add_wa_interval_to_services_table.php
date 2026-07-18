<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ❌ HAPUS kolom yang tidak dipakai (karena interval GLOBAL)
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // ❌ HAPUS kolom-kolom ini
            $table->dropColumn([
                'wa_interval_minutes',              // Interval per-service (tidak dipakai)
                'last_interval_checked_at',         // Tracking interval per-service
                'last_interval_status',             // Status interval per-service
                'interval_wa_sent_in_this_cycle',   // Flag WA per-service
            ]);
        });
    }

    /**
     * Reverse the migrations.
     * 
     * 🔄 KEMBALIKAN kolom jika rollback
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Tambahkan kembali kolom yang dihapus
            $table->integer('wa_interval_minutes')->default(0)->nullable()
                ->after('last_wa_status')
                ->comment('Interval WA dalam menit (per-service)');
            
            $table->timestamp('last_interval_checked_at')->nullable()
                ->after('wa_interval_minutes')
                ->comment('Waktu terakhir pengecekan interval');
            
            $table->string('last_interval_status')->nullable()
                ->after('last_interval_checked_at')
                ->comment('Status service saat interval terakhir');
            
            $table->boolean('interval_wa_sent_in_this_cycle')->default(false)
                ->after('last_interval_status')
                ->comment('Apakah WA sudah terkirim di interval ini?');
        });
    }
};