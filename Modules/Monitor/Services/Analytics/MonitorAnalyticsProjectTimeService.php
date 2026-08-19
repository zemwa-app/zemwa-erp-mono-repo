<?php

namespace Modules\Monitor\Services\Analytics;

use App\Models\Project;
use App\Models\ProjectTimeLog;
use Modules\Monitor\Services\Analytics\PeriodHelper;
use Carbon\Carbon;
use Modules\Monitor\Services\MonitorPermissionScope;
use Modules\Monitor\Support\ListSearch;

class MonitorAnalyticsProjectTimeService
{
    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }
    /**
     * @return array<string, mixed>
     */
    public function getProjectTime(int $companyId, string $period, string $search = ''): array
    {
        [$start, $end] = PeriodHelper::resolve($period);

        if ($this->permissionScope->permission() === 'none') {
            return [
                'period' => $period,
                'rows' => [],
            ];
        }

        $logsQuery = ProjectTimeLog::query()
            ->where('company_id', $companyId)
            ->whereNotNull('project_id')
            ->where('start_time', '>=', $start)
            ->where('start_time', '<=', $end)
            ->whereNotNull('end_time');

        $this->permissionScope->applyAgentDataScope($logsQuery, $companyId);
        $logs = $logsQuery->get()->groupBy('project_id');

        $projectIds = $logs->keys()->filter()->all();
        $projects = Project::whereIn('id', $projectIds)->get(['id', 'project_name', 'hours_allocated'])->keyBy('id');

        $rows = [];

        foreach ($logs as $projectId => $projectLogs) {
            $project = $projects->get($projectId);
            $seconds = (int) $projectLogs->sum(fn ($log) => (int) (($log->total_hours ?? 0) * 3600) + (($log->total_minutes ?? 0) * 60));
            $loggedHours = round($seconds / 3600, 2);
            $budgetHours = (float) ($project?->hours_allocated ?? 0);
            $status = $this->budgetStatus($loggedHours, $budgetHours);

            $rows[] = [
                'project_id' => $projectId,
                'project_name' => $project?->project_name ?? __('monitor::app.unknownProject'),
                'logged_hours' => $loggedHours,
                'logged_label' => MonitorAnalyticsHelper::formatDuration($seconds),
                'budget_hours' => $budgetHours > 0 ? $budgetHours : null,
                'budget_pct' => $budgetHours > 0 ? min(100, round(($loggedHours / $budgetHours) * 100)) : null,
                'status' => $status['key'],
                'status_label' => $status['label'],
                'status_class' => $status['class'],
                'status_icon' => $status['icon'],
            ];
        }

        usort($rows, fn ($a, $b) => $b['logged_hours'] <=> $a['logged_hours']);

        return [
            'period' => $period,
            'rows' => ListSearch::filterRows($rows, ListSearch::normalize($search), ['project_name', 'status_label']),
        ];
    }

    /**
     * @return array{key: string, label: string, class: string, icon: ?string}
     */
    private function budgetStatus(float $loggedHours, float $budgetHours): array
    {
        if ($budgetHours <= 0) {
            return [
                'key' => 'none',
                'label' => __('monitor::app.budgetNone'),
                'class' => 'text-gray-500',
                'icon' => null,
            ];
        }

        $pct = ($loggedHours / $budgetHours) * 100;

        if ($pct >= 100) {
            return [
                'key' => 'over',
                'label' => __('monitor::app.budgetOver'),
                'class' => 'text-red-600',
                'icon' => 'exclamation-circle',
            ];
        }

        if ($pct >= 75) {
            return [
                'key' => 'near',
                'label' => __('monitor::app.budgetNear'),
                'class' => 'text-amber-600',
                'icon' => 'exclamation-triangle',
            ];
        }

        return [
            'key' => 'on_track',
            'label' => __('monitor::app.budgetOnTrack'),
            'class' => 'text-green-600',
            'icon' => null,
        ];
    }
}
