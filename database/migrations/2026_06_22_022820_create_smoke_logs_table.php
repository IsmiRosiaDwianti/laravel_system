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
        Schema::create('smoke_logs', function (Blueprint $table) {

            $table->id();

            // ✅ UBAH: smoke_device_id → device_id
            $table->foreignId('device_id')
                  ->constrained('smoke_devices')
                  ->cascadeOnDelete();

            $table->integer('smoke_value');

            // ✅ TAMBAHKAN: WARNING
            $table->enum('status', [
                'NORMAL',
                'WARNING',    // ← TAMBAHKAN INI
                'DANGER'
            ])->default('NORMAL');  // ← TAMBAHKAN DEFAULT

            // ✅ TAMBAHKAN: message
            $table->text('message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smoke_logs');
    }
};