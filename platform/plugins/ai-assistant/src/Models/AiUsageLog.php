<?php

namespace Botble\AiAssistant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $table = 'ai_usage_logs';
    protected $fillable = [
        'api_key_id', 'model', 'request_type', 'prompt', 'response',
        'input_tokens', 'output_tokens', 'status', 'error_message',
        'cost', 'response_time_ms', 'context_type', 'context_id',
        'context_field', 'user_id'
    ];
    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost' => 'float',
        'response_time_ms' => 'integer',
    ];

    public const UPDATED_AT = null;

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(AiApiKey::class, 'api_key_id');
    }

    public function getTotalTokens(): int
    {
        return $this->input_tokens + $this->output_tokens;
    }
}
