<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('service_logs', 'is_status_change')) {
                $table->boolean('is_status_change')
                      ->default(false)
                      ->after('message')
                      ->comment('Menandakan apakah ini adalah perubahan status');
            }

            if (!Schema::hasColumn('service_logs', 'previous_status')) {
                $table->string('previous_status')
                      ->nullable()
                      ->after('is_status_change')
                      ->comment('Status sebelumnya sebelum perubahan');
            }

            // Tambahkan index untuk mempercepat query
            $table->index('is_status_change');
            $table->index(['service_id', 'is_status_change']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropIndex(['is_status_change']);
            $table->dropIndex(['service_id', 'is_status_change']);
            $table->dropColumn(['is_status_change', 'previous_status']);
        });
    }
};