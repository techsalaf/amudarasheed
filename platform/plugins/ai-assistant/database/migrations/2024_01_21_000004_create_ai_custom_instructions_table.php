<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_custom_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Professional Tone", "Casual & Friendly"
            $table->text('instruction'); // the custom instruction text
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_custom_instructions');
    }
};
