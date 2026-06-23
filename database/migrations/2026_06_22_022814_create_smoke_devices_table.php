<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smoke_devices', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('location');

            $table->integer('threshold')
                  ->default(400);

            $table->float('smoke_value')
                  ->default(0);

            $table->string('status')
                  ->default('NORMAL');

            $table->string('device_status')
                  ->default('OFFLINE');

            $table->timestamp('last_seen_at')
                  ->nullable();

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smoke_devices');
    }
};