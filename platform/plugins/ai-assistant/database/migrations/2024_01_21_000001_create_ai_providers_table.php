<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // openai, gemini, claude, deepseek, openrouter, grok
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('config')->nullable(); // provider-specific config (base URLs, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // for ordering/fallback
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
