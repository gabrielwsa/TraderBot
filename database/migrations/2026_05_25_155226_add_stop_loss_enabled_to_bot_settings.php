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
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->boolean('stop_loss_enabled')->default(true)->after('take_profit_pct');
        });
    }

    public function down(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->dropColumn('stop_loss_enabled');
        });
    }
};
