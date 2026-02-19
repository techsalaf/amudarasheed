<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained('ai_api_keys')->cascadeOnDelete();
            $table->string('model');
            $table->string('request_type'); // text_generation, image_generation
            $table->text('prompt')->nullable();
            $table->text('response')->nullable();
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->string('status'); // success, failed, rate_limited
            $table->text('error_message')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('context_type')->nullable(); // blog_post, page, product, etc.
            $table->string('context_id')->nullable();
            $table->string('context_field')->nullable(); // title, description, etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('api_key_id');
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
