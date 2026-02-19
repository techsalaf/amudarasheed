@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-edit"></i> Edit API Key
                    </h2>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <form method="POST" action="{{ route('ai-assistant.keys.update', $apiKey->id) }}" class="card-body">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">AI Provider *</label>
                                <select name="provider_id" class="form-control @error('provider_id') is-invalid @enderror" required>
                                    <option value="">-- Select Provider --</option>
                                    @foreach ($providers as $provider)
                                        <option value="{{ $provider->id }}" @selected($apiKey->provider_id == $provider->id)>
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
                                    value="{{ old('label', $apiKey->label) }}" placeholder="e.g., Production Key">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">API Key (Leave blank to keep current)</label>
                                <input type="password" name="key" class="form-control" 
                                    placeholder="Paste new API key to update">
                                <small class="form-hint">Only change if you want to update the key</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control" 
                                    value="{{ old('model', $apiKey->model) }}" placeholder="e.g., gpt-4">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Monthly Token Limit</label>
                                        <input type="number" name="monthly_token_limit" class="form-control" 
                                            value="{{ old('monthly_token_limit', $apiKey->monthly_token_limit) }}" min="0">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <input type="number" name="priority" class="form-control" 
                                            value="{{ old('priority', $apiKey->priority) }}" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                        @checked($apiKey->is_active) id="isActive">
                                    <label class="form-check-label" for="isActive">
                                        Activate this key
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reset_token_usage" value="1" id="resetTokens">
                                    <label class="form-check-label" for="resetTokens">
                                        Reset token usage counter (Current: {{ number_format($apiKey->monthly_tokens_used) }})
                                    </label>
                                </div>
                            </div>

                            <div class="form-footer">
                                <a href="{{ route('ai-assistant.keys.index') }}" class="btn btn-link">Back</a>
                                <button type="submit" class="btn btn-primary">Update API Key</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h3 class="card-title">Key Info</h3>
                            <dl class="row">
                                <dt class="col-8">Created:</dt>
                                <dd class="col-4">{{ $apiKey->created_at->diffForHumans() }}</dd>
                                
                                <dt class="col-8">Updated:</dt>
                                <dd class="col-4">{{ $apiKey->updated_at->diffForHumans() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/core/plugins/ai-assistant/js/ai-provider-models.js') }}"></script>
@endsection
