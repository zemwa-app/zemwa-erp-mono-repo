<?php

namespace Modules\Monitor\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\RestAPI\Entities\AgentScreenshot;

class MonitorScreenshotService
{
    public const PER_PAGE = 48;

    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }

    /**
     * Company-local calendar day as UTC bounds for comparing against UTC-stored captured_at.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function dayBoundsUtc(string $dateYmd, string $timezone): array
    {
        $dayStart = Carbon::createFromFormat('Y-m-d', $dateYmd, $timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();

        return [
            $dayStart->copy()->setTimezone('UTC'),
            $dayEnd->copy()->setTimezone('UTC'),
        ];
    }

    /**
     * @param  array{employee?: string, date?: string, category?: string, search?: string}  $filters
     */
    public function paginate(int $companyId, array $filters): LengthAwarePaginator
    {
        $employeeId = ($filters['employee'] ?? 'all') !== 'all' ? (int) $filters['employee'] : null;
        $timezone = company()->timezone;
        $dateYmd = $filters['date'] ?? today()->timezone($timezone)->toDateString();
        [$dayStartUtc, $dayEndUtc] = self::dayBoundsUtc($dateYmd, $timezone);
        $category = $filters['category'] ?? 'all';
        $search = trim($filters['search'] ?? '');

        $query = AgentScreenshot::query()
            ->where('company_id', $companyId);
        $this->permissionScope->applyAgentDataScope($query, $companyId);
        $query->with([
                'user:id,name',
                'task:id,heading,project_id,board_column_id,status,priority,due_date',
                'task.project:id,project_name',
                'task.boardColumn:id,column_name',
            ])
            ->where('captured_at', '>=', $dayStartUtc->toDateTimeString())
            ->where('captured_at', '<=', $dayEndUtc->toDateTimeString())
            ->orderByDesc('captured_at');

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        if ($category !== 'all' && in_array($category, ['productive', 'unproductive', 'neutral'], true)) {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('active_app', 'like', '%' . $search . '%')
                    ->orWhere('window_title', 'like', '%' . $search . '%');
            });
        }

        $paginator = $query->paginate(self::PER_PAGE)->withQueryString();
        $paginator->getCollection()->transform(
            fn (AgentScreenshot $screenshot) => $this->mapScreenshot($screenshot)
        );

        return $paginator;
    }

    /**
     * @return array<string, mixed>
     */
    public function mapScreenshot(AgentScreenshot $screenshot): array
    {
        $user = $screenshot->relationLoaded('user') ? $screenshot->user : null;

        $capturedAt = $screenshot->captured_at->timezone(company()->timezone);
        $taskMeta = $this->mapTaskMeta($screenshot);
        $productivityTone = match ($screenshot->category) {
            'productive' => 'green',
            'unproductive' => 'red',
            default => 'amber',
        };

        return [
            'id' => $screenshot->id,
            'user_id' => $screenshot->user_id,
            'employee_name' => $user?->name,
            'captured_at' => $capturedAt->format(company()->date_format . ' ' . company()->time_format),
            'captured_date' => $capturedAt->format(company()->date_format),
            'captured_time' => $capturedAt->format(company()->time_format),
            'captured_timestamp' => $capturedAt->timestamp,
            'active_app' => $screenshot->active_app,
            'window_title' => $screenshot->window_title,
            'category' => $screenshot->category,
            'productivity_label' => self::categoryLabel($screenshot->category),
            'productivity_tone' => $productivityTone,
            'task' => $taskMeta,
            'task_heading' => $taskMeta['heading'] ?? null,
            'task_project' => $taskMeta['project_name'] ?? null,
            'task_status' => $taskMeta['status'] ?? null,
            'task_priority' => $taskMeta['priority'] ?? null,
            'task_due_date' => $taskMeta['due_date'] ?? null,
            'task_url' => $taskMeta['url'] ?? null,
            'project_name' => $taskMeta['project_name'] ?? null,
            'thumbnail_url' => $screenshot->thumbnailUrl(),
            'full_url' => $screenshot->fullUrl(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function mapTaskMeta(AgentScreenshot $screenshot): ?array
    {
        if (!$screenshot->task_id) {
            return null;
        }

        $task = $screenshot->relationLoaded('task') ? $screenshot->task : null;

        if (!$task) {
            return null;
        }

        $project = $task->relationLoaded('project') ? $task->project : null;
        $column = $task->relationLoaded('boardColumn') ? $task->boardColumn : null;

        return [
            'id' => $task->id,
            'heading' => $task->heading,
            'project_id' => $task->project_id,
            'project_name' => $project?->project_name,
            'status' => $column?->column_name ?? $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date
                ? Carbon::parse($task->due_date)->timezone(company()->timezone)->format(company()->date_format)
                : null,
            'url' => route('tasks.show', $task->id),
        ];
    }

    public static function categoryLabel(?string $category): string
    {
        return match ($category) {
            'productive' => __('monitor::app.categoryProductive'),
            'unproductive' => __('monitor::app.categoryUnproductive'),
            default => __('monitor::app.categoryNeutral'),
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function getEmployeeOptions(int $companyId)
    {
        return $this->permissionScope->getEmployees($companyId, null, ['id', 'name']);
    }
}
