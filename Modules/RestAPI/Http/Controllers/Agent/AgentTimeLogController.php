<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use App\Models\ProjectTimeLog;
use App\Models\Task;
use App\Scopes\CompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Http\Requests\Agent\TimeLogRequest;

class AgentTimeLogController extends Controller
{
    /**
     * Batch upload completed task time logs from the desktop agent buffer.
     */
    public function store(TimeLogRequest $request): JsonResponse
    {
        $user = $request->user();
        $entries = $request->all();
        $inserted = 0;

        foreach ($entries as $entry) {
            $taskId = $entry['task_id'] ?? null;
            if (!$taskId) {
                continue;
            }

            $task = Task::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $user->company_id)
                ->find($taskId);
            if (!$task) {
                continue;
            }

            $durationSeconds = (int) ($entry['duration_seconds'] ?? 0);
            if ($durationSeconds < 1) {
                $durationSeconds = 1;
            }

            $start = isset($entry['started_at'])
                ? \Carbon\Carbon::parse($entry['started_at'])
                : now();
            $end = isset($entry['ended_at'])
                ? \Carbon\Carbon::parse($entry['ended_at'])
                : $start->copy()->addSeconds($durationSeconds);

            $totalMinutes = (int) max(1, ceil($durationSeconds / 60));
            $totalHours = (int) floor($durationSeconds / 3600);

            ProjectTimeLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'start_time' => $start,
                'end_time' => $end,
                'total_hours' => $totalHours,
                'total_minutes' => $totalMinutes,
                'memo' => $entry['note'] ?? '',
                'hourly_rate' => $user->hourly_rate ?? 0,
                'added_by' => $user->id,
            ]);
            $inserted++;
        }

        return response()->json(['inserted' => $inserted], 201);
    }
}
