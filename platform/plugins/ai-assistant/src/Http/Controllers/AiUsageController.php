<?php

namespace Botble\AiAssistant\Http\Controllers;

use Botble\AiAssistant\Models\AiUsageLog;
use Illuminate\Http\Request;

class AiUsageController
{
    /**
     * Show usage logs and analytics
     */
    public function index(Request $request)
    {
        $query = AiUsageLog::with(['apiKey.provider'])
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by provider
        if ($request->filled('provider_id')) {
            $query->whereHas('apiKey', function ($q) {
                $q->where('provider_id', request()->input('provider_id'));
            });
        }

        $logs = $query->paginate(50);

        // Calculate statistics
        $stats = [
            'total_requests' => AiUsageLog::count(),
            'successful_requests' => AiUsageLog::where('status', 'success')->count(),
            'failed_requests' => AiUsageLog::where('status', 'failed')->count(),
            'total_tokens' => AiUsageLog::sum(\DB::raw('input_tokens + output_tokens')),
            'total_cost' => AiUsageLog::sum('cost'),
            'avg_response_time' => AiUsageLog::avg('response_time_ms'),
        ];

        return view('plugins/ai-assistant::admin.usage.index', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    /**
     * Export usage logs as CSV
     */
    public function export(Request $request)
    {
        $query = AiUsageLog::with(['apiKey.provider'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $logs = $query->get();

        $csv = "Date,Model,Provider,Request Type,Status,Input Tokens,Output Tokens,Cost,Response Time (ms),User ID\n";

        foreach ($logs as $log) {
            $csv .= "{$log->created_at},{$log->model},{$log->apiKey->provider->display_name}," .
                    "{$log->request_type},{$log->status},{$log->input_tokens}," .
                    "{$log->output_tokens},{$log->cost},{$log->response_time_ms}," .
                    "{$log->user_id}\n";
        }

        return response()->streamDownload(
            fn() => print($csv),
            'ai-usage-' . now()->format('Y-m-d-His') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Clear old logs
     */
    public function clearOldLogs(Request $request)
    {
        $validated = $request->validate([
            'days_old' => 'required|integer|min:7|max:365',
        ]);

        $cutoffDate = now()->subDays($validated['days_old']);
        $deletedCount = AiUsageLog::where('created_at', '<', $cutoffDate)->delete();

        return redirect()
            ->route('ai-assistant.usage.index')
            ->with('success', "Deleted {$deletedCount} logs older than {$validated['days_old']} days");
    }
}
