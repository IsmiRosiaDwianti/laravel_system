<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smoke_devices', function (Blueprint $table) {
            // HAPUS '->after('last_status')' karena kolom last_status tidak ada
            $table->string('last_status_notified')
                ->default('NORMAL');
                // ->after('last_status'); // <-- HAPUS BARIS INI
        });
    }

    public function down(): void
    {
        Schema::table('smoke_devices', function (Blueprint $table) {
            $table->dropColumn('last_status_notified');
        });
    }
};