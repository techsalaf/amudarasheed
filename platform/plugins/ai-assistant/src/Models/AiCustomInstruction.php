<?php

namespace Botble\AiAssistant\Models;

use Illuminate\Database\Eloquent\Model;

class AiCustomInstruction extends Model
{
    protected $table = 'ai_custom_instructions';
    protected $fillable = ['name', 'instruction', 'description', 'is_active', 'order'];
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
