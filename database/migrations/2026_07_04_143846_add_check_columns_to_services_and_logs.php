<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom ke tabel services
        Schema::table('services', function (Blueprint $table) {
            $table->timestamp('last_check_at')->nullable()->after('last_message');
        });

        // Tambah kolom ke tabel service_logs
        Schema::table('service_logs', function (Blueprint $table) {
            $table->timestamp('checked_at')->nullable()->after('action');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('last_check_at');
        });

        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropColumn('checked_at');
        });
    }
};