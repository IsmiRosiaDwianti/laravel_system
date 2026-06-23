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

            $table->foreignId('smoke_device_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->integer('smoke_value');

            $table->enum('status', [
                'NORMAL',
                'DANGER'
            ]);

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