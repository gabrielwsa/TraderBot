<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->boolean('trailing_stop_enabled')->default(true)->after('take_profit_pct');
            $table->float('trailing_stop_pct')->default(1.0)->after('trailing_stop_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->dropColumn(['trailing_stop_enabled', 'trailing_stop_pct']);
        });
    }
};
