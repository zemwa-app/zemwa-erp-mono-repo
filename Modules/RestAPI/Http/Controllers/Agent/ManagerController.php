<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use App\Models\EmployeeDetails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\RestAPI\Entities\AgentActivityLog;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Entities\AgentHeartbeat;
use Modules\RestAPI\Entities\AgentScreenshot;

class ManagerController extends Controller
{
    public function employees(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $employees = User::where('company_id', $companyId)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'employee');
            })
            ->where('status', 'active')
            ->get();

        $result = $employees->map(function ($employee) {
            $latestHeartbeat = AgentHeartbeat::where('user_id', $employee->id)
                ->latest('created_at')
                ->first();

            $isOnline = $latestHeartbeat && $latestHeartbeat->created_at->diffInMinutes(now()) < 2;

            $todayScore = AgentActivityWindow::where('user_id', $employee->id)
                ->whereDate('window_start', today())
                ->where('is_idle', false)
                ->avg('activity_pct');

            $employeeDetail = EmployeeDetails::where('user_id', $employee->id)->first();
            $department = $employeeDetail?->department?->team_name ?? null;

            return [
                'id' => $employeeDetail?->employee_id ?? ('E' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)),
                'name' => $employee->name,
                'email' => $employee->email,
                'department' => $department,
                'is_online' => $isOnline,
                'is_idle' => $latestHeartbeat?->is_idle ?? false,
                'is_paused' => $latestHeartbeat?->is_paused ?? false,
                'active_app' => $latestHeartbeat?->active_app,
                'activity_pct_today' => round($todayScore ?? 0, 1),
                'last_seen_at' => $latestHeartbeat?->created_at?->toIso8601String(),
                'agent_version' => $latestHeartbeat?->agent_version,
            ];
        });

        return response()->json(['employees' => $result]);
    }

    public function employeeTimeline(Request $request, int $id): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $this->authorizeManagerAccess($request, $id);
        $date = Carbon::parse($request->query('date'));

        $entries = AgentActivityLog::where('user_id', $id)
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

    public function employeeScreenshots(Request $request, int $id): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $this->authorizeManagerAccess($request, $id);
        $date = Carbon::parse($request->query('date'));

        $screenshots = AgentScreenshot::where('user_id', $id)
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
                'thumbnail_url' => $screenshot->thumbnail_path
                    ? Storage::disk('public')->url($screenshot->thumbnail_path)
                    : null,
                'full_url' => Storage::disk('public')->url($screenshot->file_path),
            ];
        });

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'total' => $screenshots->count(),
            'screenshots' => $items,
        ]);
    }

    public function employeeScores(Request $request, int $id): JsonResponse
    {
        $request->validate(['days' => 'nullable|integer|min:1|max:90']);

        $this->authorizeManagerAccess($request, $id);
        $days = (int) $request->query('days', 30);
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        $windows = AgentActivityWindow::where('user_id', $id)
            ->whereBetween('window_start', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get()
            ->groupBy(fn($w) => $w->window_start->format('Y-m-d'));

        $scores = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayWindows = $windows->get($dateKey);

            if (!$dayWindows || $dayWindows->isEmpty()) {
                $scores[] = ['date' => $dateKey, 'score' => 0, 'active_seconds' => 0, 'idle_seconds' => 0];
                continue;
            }

            $nonIdle = $dayWindows->where('is_idle', false);
            $score = $nonIdle->isNotEmpty() ? round($nonIdle->avg('activity_pct'), 1) : 0;

            $activeSeconds = $nonIdle->sum(fn($w) => $w->window_start->diffInSeconds($w->window_end));
            $idleSeconds = $dayWindows->where('is_idle', true)->sum(fn($w) => $w->window_start->diffInSeconds($w->window_end));

            $scores[] = [
                'date' => $dateKey,
                'score' => $score,
                'active_seconds' => (int) $activeSeconds,
                'idle_seconds' => (int) $idleSeconds,
            ];
        }

        return response()->json(['scores' => $scores]);
    }

    public function teamReport(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $user = $request->user();
        $companyId = $user->company_id;
        $date = Carbon::parse($request->query('date'));

        $employeeIds = User::where('company_id', $companyId)
            ->whereHas('roles', fn($q) => $q->where('name', 'employee'))
            ->where('status', 'active')
            ->pluck('id');

        $totalEmployees = $employeeIds->count();

        $latestHeartbeats = AgentHeartbeat::whereIn('user_id', $employeeIds)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->get();

        $onlineNow = $latestHeartbeats->count();
        $idleNow = $latestHeartbeats->where('is_idle', true)->count();
        $pausedNow = $latestHeartbeats->where('is_paused', true)->count();
        $offline = $totalEmployees - $onlineNow;

        $dayWindows = AgentActivityWindow::whereIn('user_id', $employeeIds)
            ->whereDate('window_start', $date)
            ->where('is_idle', false)
            ->get();

        $teamAvgScore = $dayWindows->isNotEmpty() ? round($dayWindows->avg('activity_pct'), 1) : 0;

        $dayLogs = AgentActivityLog::whereIn('user_id', $employeeIds)
            ->whereDate('started_at', $date)
            ->get();

        $appUsage = $dayLogs->groupBy('app_name')->map(function ($logs, $app) {
            return [
                'app' => $app,
                'total_minutes' => (int) round($logs->sum('duration_seconds') / 60),
            ];
        });

        $productive = $appUsage->filter(fn($item) => $dayLogs->where('app_name', $item['app'])->first()?->category === 'productive')
            ->sortByDesc('total_minutes')->take(5)->values();

        $unproductive = $appUsage->filter(fn($item) => $dayLogs->where('app_name', $item['app'])->first()?->category === 'unproductive')
            ->sortByDesc('total_minutes')->take(5)->values();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'team_avg_score' => $teamAvgScore,
            'total_employees' => $totalEmployees,
            'online_now' => $onlineNow,
            'idle_now' => $idleNow,
            'paused_now' => $pausedNow,
            'offline' => $offline,
            'top_productive_apps' => $productive,
            'top_unproductive_apps' => $unproductive,
        ]);
    }

    private function authorizeManagerAccess(Request $request, int $employeeUserId): void
    {
        $manager = $request->user();
        $employee = User::findOrFail($employeeUserId);

        if ($employee->company_id !== $manager->company_id) {
            abort(403, 'Unauthorized access to this employee');
        }
    }
}
