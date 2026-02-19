<?php

namespace Botble\AiAssistant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    protected $table = 'ai_providers';
    protected $fillable = ['name', 'display_name', 'description', 'config', 'is_active', 'priority'];
    protected $casts = [
        'config' => 'json',
        'is_active' => 'boolean',
    ];

    public function apiKeys(): HasMany
    {
        return $this->hasMany(AiApiKey::class, 'provider_id');
    }

    public function activeKeys(): HasMany
    {
        return $this->apiKeys()->where('is_active', true)->orderBy('priority', 'asc');
    }
}
