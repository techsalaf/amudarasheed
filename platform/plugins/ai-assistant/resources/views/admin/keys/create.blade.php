@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-plus"></i> Add API Key
                    </h2>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <form method="POST" action="{{ route('ai-assistant.keys.store') }}" class="card-body">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">AI Provider *</label>
                                <select name="provider_id" class="form-control @error('provider_id') is-invalid @enderror" required>
                                    <option value="">-- Select Provider --</option>
                                    @foreach ($providers as $provider)
                                        <option value="{{ $provider->id }}" @selected(old('provider_id') == $provider->id)>
                                            {{ $provider->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provider_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Label (Optional)</label>
                                <input type="text" name="label" class="form-control" 
                                    value="{{ old('label') }}" placeholder="e.g., Production Key, Testing Key">
                                <small class="form-hint">A friendly name to identify this key</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">API Key *</label>
                                <input type="password" name="key" class="form-control @error('key') is-invalid @enderror" 
                                    required placeholder="Paste your API key here">
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Model (Optional)</label>
                                <input type="text" name="model" class="form-control" 
                                    value="{{ old('model') }}" placeholder="e.g., gpt-4, claude-3-opus">
                                <small class="form-hint">Specific model to use; leave blank for provider default</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Monthly Token Limit (Optional)</label>
                                        <input type="number" name="monthly_token_limit" class="form-control" 
                                            value="{{ old('monthly_token_limit') }}" min="0" placeholder="Leave blank for unlimited">
                                        <small class="form-hint">Maximum tokens per month for this key</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <input type="number" name="priority" class="form-control" 
                                            value="{{ old('priority') ?? 0 }}" min="0">
                                        <small class="form-hint">Lower number = higher priority for fallback</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                        @checked(old('is_active', true)) id="isActive">
                                    <label class="form-check-label" for="isActive">
                                        Activate this key
                                    </label>
                                </div>
                            </div>

                            <div class="form-footer">
                                <a href="{{ route('ai-assistant.keys.index') }}" class="btn btn-link">Back</a>
                                <button type="submit" class="btn btn-primary">Add API Key</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Need API Keys?</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled space-y-2">
                                <li><a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Keys →</a></li>
                                <li><a href="https://aistudio.google.com/app/apikey" target="_blank">Google Gemini Keys →</a></li>
                                <li><a href="https://console.anthropic.com/keys" target="_blank">Claude Keys →</a></li>
                                <li><a href="https://platform.deepseek.com/" target="_blank">DeepSeek Keys →</a></li>
                                <li><a href="https://openrouter.ai/keys" target="_blank">OpenRouter Keys →</a></li>
                                <li><a href="https://console.x.ai/" target="_blank">Grok Keys →</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/core/plugins/ai-assistant/js/ai-provider-models.js') }}"></script>
@endsection
