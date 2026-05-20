<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(false)->after('pair_blacklist');
            $table->string('ai_provider')->default('ollama')->after('ai_enabled'); // ollama | claude | openai
            $table->string('ai_model')->default('llama3.1')->after('ai_provider');
            $table->string('ai_base_url')->default('http://localhost:11434')->after('ai_model');
            $table->string('ai_api_key')->nullable()->after('ai_base_url');
        });
    }

    public function down(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->dropColumn(['ai_enabled', 'ai_provider', 'ai_model', 'ai_base_url', 'ai_api_key']);
        });
    }
};
