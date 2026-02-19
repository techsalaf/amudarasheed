<?php

namespace Botble\AiAssistant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiApiKey extends Model
{
    protected $table = 'ai_api_keys';
    protected $fillable = [
        'provider_id', 'label', 'key_encrypted', 'model',
        'is_active', 'priority', 'monthly_token_limit', 'monthly_tokens_used',
        'tokens_reset_at', 'note'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'tokens_reset_at' => 'datetime',
    ];

    protected $hidden = ['key_encrypted'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class, 'api_key_id');
    }

    public function getRemainingTokens(): ?int
    {
        if (!$this->monthly_token_limit) {
            return null;
        }

        return max(0, $this->monthly_token_limit - $this->monthly_tokens_used);
    }

    public function hasTokensAvailable(): bool
    {
        if (!$this->monthly_token_limit) {
            return true; // unlimited
        }

        return $this->getRemainingTokens() > 0;
    }

    public function getDecryptedKey(): string
    {
        return decrypt($this->key_encrypted);
    }
}
