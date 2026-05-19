<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('pair');
            $table->enum('signal', ['BUY', 'SELL', 'HOLD']);
            $table->decimal('ema9', 18, 8)->nullable();
            $table->decimal('ema21', 18, 8)->nullable();
            $table->decimal('rsi', 8, 4)->nullable();
            $table->decimal('macd_hist', 18, 8)->nullable();
            $table->decimal('price', 18, 8)->nullable();
            $table->boolean('traded')->default(false);
            $table->string('skip_reason')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
