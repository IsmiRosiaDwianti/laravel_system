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
        Schema::create('service_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('service_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->string('status');

            $table->string('response_code')
                  ->nullable();

            $table->float('response_time')
                  ->nullable();

            $table->text('message')
                  ->nullable();

            // ========== TAMBAHKAN INI ==========
            // Kolom untuk menandai apakah ini log perubahan status
            $table->boolean('is_status_change')
                  ->default(false)
                  ->after('message')
                  ->comment('Menandakan apakah ini adalah perubahan status');

            // Kolom untuk menyimpan status sebelumnya
            $table->string('previous_status')
                  ->nullable()
                  ->after('is_status_change')
                  ->comment('Status sebelumnya sebelum perubahan');

            // Tambahkan index untuk mempercepat query
            $table->index('is_status_change');
            $table->index(['service_id', 'is_status_change']);
            // ========== SAMPAI SINI ==========

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};