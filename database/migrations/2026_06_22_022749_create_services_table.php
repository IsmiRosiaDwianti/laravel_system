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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // Nama service
            $table->string('name');

            // URL website atau IP server
            $table->string('target');

            // http atau ping
            $table->enum('type', ['http', 'ping']);

            // Status terakhir
            $table->enum('last_status', [
                'UP',
                'WARNING',
                'DOWN',
                'UNKNOWN'
            ])->default('UNKNOWN');

            // HTTP Code (200, 404, dll)
            $table->string('last_code')->nullable();

            // Response time
            $table->float('last_response_time')->nullable();

            // Pesan terakhir
            $table->text('last_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};