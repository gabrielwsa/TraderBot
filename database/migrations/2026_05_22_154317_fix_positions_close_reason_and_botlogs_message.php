<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('close_reason', 30)->nullable()->change();
        });

        Schema::table('bot_logs', function (Blueprint $table) {
            $table->text('message')->change();
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->enum('close_reason', ['stop_loss', 'take_profit', 'signal', 'manual'])->nullable()->change();
        });

        Schema::table('bot_logs', function (Blueprint $table) {
            $table->string('message')->change();
        });
    }
};
