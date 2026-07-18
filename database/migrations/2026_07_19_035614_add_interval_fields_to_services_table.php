<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // ✅ TAMBAHKAN kolom (BUKAN HAPUS!)
            if (!Schema::hasColumn('services', 'wa_interval_minutes')) {
                $table->integer('wa_interval_minutes')->default(0);
            }
            if (!Schema::hasColumn('services', 'last_interval_checked_at')) {
                $table->timestamp('last_interval_checked_at')->nullable();
            }
            if (!Schema::hasColumn('services', 'last_interval_status')) {
                $table->string('last_interval_status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'wa_interval_minutes',
                'last_interval_checked_at',
                'last_interval_status',
            ]);
        });
    }
};