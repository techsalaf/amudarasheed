@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-cog"></i> AI Assistant Settings
                    </h2>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <form method="POST" action="{{ route('ai-assistant.settings.update') }}" class="card-body">
                            @csrf
                            @method('POST')

                            <!-- General Settings -->
                            <h3 class="mb-3">General Settings</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Enable AI Assistant</label>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="enable_ai" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_ai" value="1" 
                                                {{ $settings['enable_ai'] ? 'checked' : '' }} id="enableAi">
                                            <label class="form-check-label" for="enableAi">
                                                Enable all AI features
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Tokens Per Request</label>
                                        <input type="number" name="max_tokens_per_request" 
                                            value="{{ $settings['max_tokens_per_request'] ?? 1000 }}" 
                                            class="form-control" min="100" max="10000">
                                        <small class="form-hint">Maximum tokens to use per generation request</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Temperature</label>
                                        <input type="number" name="temperature" 
                                            value="{{ $settings['temperature'] ?? 0.7 }}" 
                                            step="0.1" class="form-control" min="0" max="2">
                                        <small class="form-hint">Controls randomness (0-2). Lower = more deterministic, Higher = more creative</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Default Model/Provider</label>
                                        <select name="default_model" class="form-control">
                                            <option value="">-- Auto Select --</option>
                                            @foreach ($providers as $provider)
                                                <option value="{{ $provider->name }}" 
                                                    {{ $settings['default_model'] === $provider->name ? 'selected' : '' }}>
                                                    {{ $provider->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-hint">Preferred provider; falls back to next available</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Type Availability -->
                            <h3 class="mb-3 mt-4">Available For Content Types</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_for_posts" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_for_posts" 
                                                value="1" {{ $settings['enable_for_posts'] ? 'checked' : '' }} id="enablePosts">
                                            <label class="form-check-label" for="enablePosts">
                                                Blog Posts
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_for_pages" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_for_pages" 
                                                value="1" {{ $settings['enable_for_pages'] ? 'checked' : '' }} id="enablePages">
                                            <label class="form-check-label" for="enablePages">
                                                Pages
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_for_products" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_for_products" 
                                                value="1" {{ $settings['enable_for_products'] ? 'checked' : '' }} id="enableProducts">
                                            <label class="form-check-label" for="enableProducts">
                                                Products
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_for_seo_fields" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_for_seo_fields" 
                                                value="1" {{ $settings['enable_for_seo_fields'] ? 'checked' : '' }} id="enableSeo">
                                            <label class="form-check-label" for="enableSeo">
                                                SEO Fields
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_for_custom_fields" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_for_custom_fields" 
                                                value="1" {{ $settings['enable_for_custom_fields'] ? 'checked' : '' }} id="enableCustom">
                                            <label class="form-check-label" for="enableCustom">
                                                Custom Fields
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Generation Features -->
                            <h3 class="mb-3 mt-4">Generation Features</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_text_generation" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_text_generation" 
                                                value="1" {{ $settings['enable_text_generation'] ? 'checked' : '' }} id="enableText">
                                            <label class="form-check-label" for="enableText">
                                                Enable Text Generation
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_image_generation" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_image_generation" 
                                                value="1" {{ $settings['enable_image_generation'] ? 'checked' : '' }} id="enableImage">
                                            <label class="form-check-label" for="enableImage">
                                                Enable Image Generation
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security & Privacy -->
                            <h3 class="mb-3 mt-4">Security & Privacy</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_pii_protection" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_pii_protection" 
                                                value="1" {{ $settings['enable_pii_protection'] ? 'checked' : '' }} id="enablePii">
                                            <label class="form-check-label" for="enablePii">
                                                Enable PII Protection
                                            </label>
                                        </div>
                                        <small class="form-hint">Prevent sending personal information to AI providers</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="enable_usage_tracking" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_usage_tracking" 
                                                value="1" {{ $settings['enable_usage_tracking'] ? 'checked' : '' }} id="enableTracking">
                                            <label class="form-check-label" for="enableTracking">
                                                Enable Usage Tracking
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Token Management -->
                            <h3 class="mb-3 mt-4">Token Management</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="auto_reset_tokens_monthly" value="0">
                                            <input class="form-check-input" type="checkbox" name="auto_reset_tokens_monthly" 
                                                value="1" {{ $settings['auto_reset_tokens_monthly'] ? 'checked' : '' }} id="autoReset">
                                            <label class="form-check-label" for="autoReset">
                                                Auto Reset Tokens Monthly
                                            </label>
                                        </div>
                                        <small class="form-hint">Automatically reset token counters on the 1st of each month</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <a href="{{ route('ai-assistant.settings.index') }}" class="btn btn-link">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
