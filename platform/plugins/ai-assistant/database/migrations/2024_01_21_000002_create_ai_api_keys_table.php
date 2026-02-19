<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_providers')->cascadeOnDelete();
            $table->string('label')->nullable(); // custom label for the key
            $table->text('key_encrypted'); // encrypted API key
            $table->string('model')->nullable(); // primary model for this key (e.g., gpt-4, claude-3-opus)
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // for fallback ordering
            $table->unsignedBigInteger('monthly_token_limit')->nullable();
            $table->unsignedBigInteger('monthly_tokens_used')->default(0);
            $table->timestamp('tokens_reset_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index('provider_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_api_keys');
    }
};
