@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-key"></i> API Keys Management
                    </h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('ai-assistant.keys.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Key
                    </a>
                </div>
            </div>
        </div>

        <div class="page-body">
            @if ($apiKeys->count())
                <div class="card">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Provider</th>
                                    <th>Model</th>
                                    <th>Status</th>
                                    <th>Token Usage</th>
                                    <th>Priority</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($apiKeys as $key)
                                    <tr>
                                        <td>
                                            <strong>{{ $key->label ?? 'Unnamed' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-blue">
                                                {{ $key->provider->display_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ $key->model ?? 'Default' }}</code>
                                        </td>
                                        <td>
                                            @if ($key->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($key->monthly_token_limit)
                                                <small>{{ number_format($key->monthly_tokens_used) }} / {{ number_format($key->monthly_token_limit) }}</small>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar" 
                                                        style="width: {{ ($key->monthly_tokens_used / $key->monthly_token_limit) * 100 }}%">
                                                    </div>
                                                </div>
                                            @else
                                                <small>Unlimited</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $key->priority }}
                                        </td>
                                        <td>
                                            <a href="{{ route('ai-assistant.keys.edit', $key->id) }}" 
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="{{ route('ai-assistant.keys.destroy', $key->id) }}" 
                                                style="display:inline;" onsubmit="return confirm('Delete this key?');">
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

                {{ $apiKeys->links() }}
            @else
                <div class="card">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No API keys configured yet.</p>
                        <a href="{{ route('ai-assistant.keys.create') }}" class="btn btn-primary">
                            Add Your First API Key
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
