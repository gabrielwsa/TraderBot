<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('pair');
            $table->decimal('entry_price', 18, 8);
            $table->decimal('quantity', 18, 8);
            $table->decimal('invested_usdt', 18, 8);
            $table->decimal('current_price', 18, 8)->default(0);
            $table->decimal('unrealized_pnl', 18, 8)->default(0);
            $table->decimal('unrealized_pnl_pct', 8, 4)->default(0);
            $table->decimal('stop_loss_price', 18, 8);
            $table->decimal('take_profit_price', 18, 8);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('close_price', 18, 8)->nullable();
            $table->decimal('realized_pnl', 18, 8)->nullable();
            $table->decimal('realized_pnl_pct', 8, 4)->nullable();
            $table->decimal('total_fees_usdt', 18, 8)->default(0);
            $table->enum('close_reason', ['stop_loss', 'take_profit', 'signal', 'manual'])->nullable();
            $table->string('buy_order_id')->nullable();
            $table->string('sell_order_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
