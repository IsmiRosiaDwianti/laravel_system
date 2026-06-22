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
