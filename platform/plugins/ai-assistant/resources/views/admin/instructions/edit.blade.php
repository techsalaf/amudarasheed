@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-edit"></i> Edit Custom Instruction
                    </h2>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <form method="POST" action="{{ route('ai-assistant.instructions.update', $instruction->id) }}" class="card-body">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Instruction Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $instruction->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instruction Text *</label>
                                <textarea name="instruction" class="form-control @error('instruction') is-invalid @enderror" 
                                    rows="6" required>{{ old('instruction', $instruction->instruction) }}</textarea>
                                @error('instruction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $instruction->description) }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="order" class="form-control" 
                                            value="{{ old('order', $instruction->order) }}" min="0">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                                @checked($instruction->is_active) id="isActive">
                                            <label class="form-check-label" for="isActive">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <a href="{{ route('ai-assistant.instructions.index') }}" class="btn btn-link">Back</a>
                                <button type="submit" class="btn btn-primary">Update Instruction</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
