<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->enum('environment', ['testnet', 'production'])->default('testnet');
            $table->decimal('capital_usdt', 18, 8)->default(100);
            $table->decimal('capital_per_trade_pct', 5, 2)->default(10);
            $table->unsignedTinyInteger('max_open_positions')->default(3);
            $table->decimal('stop_loss_pct', 5, 2)->default(2.00);
            $table->decimal('take_profit_pct', 5, 2)->default(4.00);
            $table->boolean('use_bnb_fees')->default(false);
            $table->decimal('min_volume_usdt', 18, 2)->default(1000000);
            $table->enum('timeframe', ['1m', '5m', '15m', '1h'])->default('15m');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_settings');
    }
};
