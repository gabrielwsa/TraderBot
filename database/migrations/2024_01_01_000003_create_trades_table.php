<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->onDelete('cascade');
            $table->string('pair');
            $table->enum('side', ['BUY', 'SELL']);
            $table->decimal('quantity', 18, 8);
            $table->decimal('price', 18, 8);
            $table->decimal('total_usdt', 18, 8);
            $table->decimal('fee_usdt', 18, 8)->default(0);
            $table->string('fee_asset')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_status')->default('FILLED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
