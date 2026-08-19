<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentScreenshot;

class EmployeeScreenshotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $user = $request->user();
        $date = Carbon::parse($request->query('date'));

        $screenshots = AgentScreenshot::where('user_id', $user->id)
            ->whereDate('captured_at', $date)
            ->orderBy('captured_at')
            ->get();

        $items = $screenshots->map(function ($screenshot) {
            return [
                'id' => $screenshot->id,
                'task_id' => $screenshot->task_id,
                'timestamp' => $screenshot->captured_at->toIso8601String(),
                'active_app' => $screenshot->active_app,
                'window_title' => $screenshot->window_title,
                'category' => $screenshot->category,
                'display_idx' => $screenshot->display_idx,
                'thumbnail_url' => $screenshot->thumbnail_path ? $screenshot->thumbnailUrl() : null,
                'full_url' => $screenshot->fullUrl(),
            ];
        });

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'total' => $screenshots->count(),
            'screenshots' => $items,
        ]);
    }
}
