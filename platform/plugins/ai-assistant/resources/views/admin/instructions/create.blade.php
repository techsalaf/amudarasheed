@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-plus"></i> Create Custom Instruction
                    </h2>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <form method="POST" action="{{ route('ai-assistant.instructions.store') }}" class="card-body">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Instruction Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name') }}" placeholder="e.g., Professional Tone" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instruction Text *</label>
                                <textarea name="instruction" class="form-control @error('instruction') is-invalid @enderror" 
                                    rows="6" placeholder="Write the instruction for the AI..." required>{{ old('instruction') }}</textarea>
                                @error('instruction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">This will be added to the AI prompt to guide content generation</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-control" rows="3" 
                                    placeholder="Brief description of this instruction...">{{ old('description') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="order" class="form-control" 
                                            value="{{ old('order', 0) }}" min="0">
                                        <small class="form-hint">Display order in dropdown (lower = first)</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                                @checked(old('is_active', true)) id="isActive">
                                            <label class="form-check-label" for="isActive">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <a href="{{ route('ai-assistant.instructions.index') }}" class="btn btn-link">Back</a>
                                <button type="submit" class="btn btn-primary">Create Instruction</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Examples</h3>
                        </div>
                        <div class="card-body">
                            <strong>Professional:</strong>
                            <small class="d-block mb-2">Write in a professional, formal tone. Use proper grammar and sophisticated vocabulary.</small>

                            <strong>SEO Optimized:</strong>
                            <small class="d-block mb-2">Include relevant keywords naturally and create compelling meta descriptions.</small>

                            <strong>Friendly:</strong>
                            <small class="d-block mb-2">Use conversational language and make it engaging and approachable.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
