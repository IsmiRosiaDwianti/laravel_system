<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Tambahkan 3 kolom yang ADA di database tapi BELUM ada di migration
            if (!Schema::hasColumn('services', 'last_wa_sent_at')) {
                $table->timestamp('last_wa_sent_at')->nullable()->after('last_check_at');
            }
            if (!Schema::hasColumn('services', 'last_wa_status')) {
                $table->string('last_wa_status')->nullable()->after('last_wa_sent_at');
            }
            if (!Schema::hasColumn('services', 'interval_wa_sent_in_this_cycle')) {
                $table->boolean('interval_wa_sent_in_this_cycle')->default(false)->after('last_wa_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'last_wa_sent_at',
                'last_wa_status',
                'interval_wa_sent_in_this_cycle'
            ]);
        });
    }
};