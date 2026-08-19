<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentActivityLog;

class EmployeeTimelineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $user = $request->user();
        $date = Carbon::parse($request->query('date'));

        $entries = AgentActivityLog::where('user_id', $user->id)
            ->whereDate('started_at', $date)
            ->orderBy('started_at')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'app_name' => $entry->app_name,
                    'process_name' => $entry->process_name,
                    'window_title' => $entry->window_title,
                    'url' => $entry->url,
                    'category' => $entry->category,
                    'started_at' => $entry->started_at->toIso8601String(),
                    'ended_at' => $entry->ended_at?->toIso8601String(),
                    'duration_seconds' => $entry->duration_seconds,
                ];
            });

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'entries' => $entries,
        ]);
    }
}
