<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use App\Models\ProjectTimeLog;
use App\Models\TaskboardColumn;
use App\Models\Task;
use App\Scopes\CompanyScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\RestAPI\Http\Controllers\ApiBaseController;

class AgentTaskController extends ApiBaseController
{
    /**
     * Tasks assigned to the logged-in employee (desktop agent).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $completedStatus = TaskboardColumn::completeColumn();

        $tasks = Task::withoutGlobalScope(CompanyScope::class)
            ->without(['company', 'project', 'users'])
            ->select([
                'tasks.id',
                'tasks.heading',
                'tasks.project_id',
                'projects.project_name',
            ])
            ->join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->where('task_users.user_id', $user->id)
            ->where('tasks.company_id', $user->company_id)
            ->where('tasks.board_column_id', '!=', $completedStatus->id)
            ->whereNull('tasks.deleted_at')
            ->groupBy('tasks.id', 'tasks.heading', 'tasks.project_id', 'projects.project_name')
            ->orderBy('tasks.heading')
            ->get();

        $taskIds = $tasks->pluck('id')->filter()->all();
        $loggedByTask = [];

        if (!empty($taskIds)) {
            $loggedByTask = ProjectTimeLog::withoutGlobalScope(CompanyScope::class)
                ->where('company_id', $user->company_id)
                ->where('user_id', $user->id)
                ->whereIn('task_id', $taskIds)
                ->whereNotNull('end_time')
                ->selectRaw('task_id, COALESCE(SUM(total_minutes), 0) as total_minutes')
                ->groupBy('task_id')
                ->pluck('total_minutes', 'task_id')
                ->all();
        }

        $data = $tasks->map(function ($task) use ($loggedByTask) {
            $minutes = (int) ($loggedByTask[$task->id] ?? 0);

            return [
                'id' => (string) $task->id,
                'task_id' => (string) $task->id,
                'name' => $task->heading,
                'task_name' => $task->heading,
                'project_id' => $task->project_id ? (string) $task->project_id : '',
                'project_name' => $task->project_name ?? '',
                'time_logged_seconds' => $minutes * 60,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
