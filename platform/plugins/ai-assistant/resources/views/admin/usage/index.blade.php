@extends('core/base::layouts.master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="fas fa-chart-line"></i> Usage & Analytics
                    </h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('ai-assistant.usage.export') }}" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>

        <div class="page-body">
            <!-- Statistics -->
            <div class="row row-deck row-cards">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-truncate">
                                <h3 class="card-title">Total Requests</h3>
                                <div class="h1">{{ number_format($stats['total_requests']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-truncate">
                                <h3 class="card-title">Successful</h3>
                                <div class="h1 text-success">{{ number_format($stats['successful_requests']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-truncate">
                                <h3 class="card-title">Failed</h3>
                                <div class="h1 text-danger">{{ number_format($stats['failed_requests']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-truncate">
                                <h3 class="card-title">Total Tokens</h3>
                                <div class="h1">{{ number_format($stats['total_tokens']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('ai-assistant.usage.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" 
                                value="{{ request('date_from') }}" placeholder="From">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" 
                                value="{{ request('date_to') }}" placeholder="To">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">-- All Status --</option>
                                <option value="success" @selected(request('status') == 'success')>Success</option>
                                <option value="failed" @selected(request('status') == 'failed')>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Usage Logs -->
            @if ($logs->count())
                <div class="card">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Model</th>
                                    <th>Provider</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Tokens</th>
                                    <th>Response Time</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>
                                            <small>{{ $log->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <code>{{ $log->model }}</code>
                                        </td>
                                        <td>
                                            {{ $log->apiKey->provider->display_name }}
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $log->request_type }}</span>
                                        </td>
                                        <td>
                                            @if ($log->status === 'success')
                                                <span class="badge badge-success">Success</span>
                                            @else
                                                <span class="badge badge-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($log->input_tokens ?? 0) }} / {{ number_format($log->output_tokens ?? 0) }}
                                        </td>
                                        <td>
                                            {{ $log->response_time_ms ? $log->response_time_ms . 'ms' : '-' }}
                                        </td>
                                        <td>
                                            {{ $log->cost ? '$' . number_format($log->cost, 4) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{ $logs->links() }}
            @else
                <div class="card">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No usage logs found.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
