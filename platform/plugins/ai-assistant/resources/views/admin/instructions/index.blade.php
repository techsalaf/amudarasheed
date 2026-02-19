@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-comment"></i> Custom Instructions
                    </h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('ai-assistant.instructions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Instruction
                    </a>
                </div>
            </div>
        </div>

        <div class="page-body">
            @if ($instructions->count())
                <div class="card">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($instructions as $instruction)
                                    <tr>
                                        <td>
                                            <strong>{{ $instruction->name }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($instruction->description, 50) }}</small>
                                        </td>
                                        <td>
                                            @if ($instruction->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $instruction->order }}
                                        </td>
                                        <td>
                                            <a href="{{ route('ai-assistant.instructions.edit', $instruction->id) }}" 
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="{{ route('ai-assistant.instructions.destroy', $instruction->id) }}" 
                                                style="display:inline;" onsubmit="return confirm('Delete this instruction?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{ $instructions->links() }}
            @else
                <div class="card">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No custom instructions created yet.</p>
                        <a href="{{ route('ai-assistant.instructions.create') }}" class="btn btn-primary">
                            Create Your First Instruction
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
