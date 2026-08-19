<?php

namespace Modules\Monitor\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Monitor\Entities\AgentDailySummary;
use Modules\Monitor\Support\EmployeeMonitoring;
use Modules\Monitor\Services\Analytics\LogoService;
use Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Entities\AgentHeartbeat;

class MonitorLiveStatusService
{
    public const ONLINE_THRESHOLD_MINUTES = 2;

    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }

    /**
     * @return array{
     *     summary: array{online: int, idle: int, paused: int, offline: int},
     *     employees: array<int, array<string, mixed>>,
     *     dashboard: array<string, mixed>
     * }
     */
    public function getLiveStatus(int $companyId, ?int $departmentId = null): array
    {
        $employees = $this->getEmployees($companyId, $departmentId);
        $userIds = $employees->pluck('id');

        if ($userIds->isEmpty()) {
            return [
                'summary' => ['online' => 0, 'idle' => 0, 'paused' => 0, 'offline' => 0],
                'employees' => [],
                'dashboard' => $this->emptyDashboard(),
            ];
        }

        $latestHeartbeats = $this->getLatestHeartbeats($companyId, $userIds);
        $todayScores = $this->getTodayScores($userIds);
        $todaySummaries = $this->getDailySummaries($companyId, $userIds, now(company()->timezone)->toDateString());
        $yesterdaySummaries = $this->getDailySummaries($companyId, $userIds, now(company()->timezone)->subDay()->toDateString());

        $summary = ['online' => 0, 'idle' => 0, 'paused' => 0, 'offline' => 0];
        $rows = [];

        foreach ($employees as $employee) {
            $heartbeat = $latestHeartbeats->get($employee->id);
            $status = $this->resolveStatus($heartbeat);
            $summary[$status]++;

            $detail = $employee->employeeDetail;
            $score = round((float) ($todayScores[$employee->id] ?? 0), 1);
            $activeApp = $heartbeat?->active_app ?: null;
            $lastSeenAt = $heartbeat?->created_at;
            $lastActivityLabel = $this->buildLastActivityLabel($status, $lastSeenAt);

            $rows[] = [
                'user_id' => $employee->id,
                'employee_code' => $detail?->employee_id ?? ('E' . str_pad((string) $employee->id, 3, '0', STR_PAD_LEFT)),
                'name' => $employee->name,
                'email' => $employee->email,
                'avatar_url' => $employee->image_url,
                'department' => $detail?->department?->team_name,
                'department_id' => $detail?->department_id,
                'status' => $status,
                'active_app' => $activeApp,
                'active_app_icon' => $this->buildAppIcon($activeApp),
                'score' => $score,
                'last_seen_at' => $lastSeenAt?->toIso8601String(),
                'last_activity_label' => $lastActivityLabel,
                'agent_version' => $heartbeat?->agent_version,
            ];
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        $dashboard = $this->buildDashboardPayload(
            companyId: $companyId,
            employees: $employees,
            rows: $rows,
            summary: $summary,
            todaySummaries: $todaySummaries,
            yesterdaySummaries: $yesterdaySummaries,
        );

        $dashboard['html'] = [
            'team_health' => view('monitor::dashboard.partials.team-health-overview', [
                'dashboard' => $dashboard,
            ])->render(),
            'attention_required' => view('monitor::dashboard.partials.attention-required', [
                'dashboard' => $dashboard,
            ])->render(),
            'workforce_snapshot' => view('monitor::dashboard.partials.workforce-snapshot', [
                'dashboard' => $dashboard,
            ])->render(),
            'workforce_analytics' => view('monitor::dashboard.partials.workforce-analytics', [
                'dashboard' => $dashboard,
            ])->render(),
        ];

        return [
            'summary' => $summary,
            'employees' => $rows,
            'dashboard' => $dashboard,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyDashboard(): array
    {
        return [
            'meta' => [
                'total_employees' => 0,
                'online_count' => 0,
                'online_percentage' => 0,
                'online_trend' => ['direction' => 'flat', 'pct' => 0, 'label' => '→ 0%', 'class' => 'text-gray-500'],
                'productivity_score' => 0,
                'productivity_trend' => ['direction' => 'flat', 'pct' => 0, 'label' => '→ 0%', 'class' => 'text-gray-500'],
                'active_applications' => 0,
                'attendance' => ['present' => 0, 'late' => 0, 'absent' => 0],
                'focus_time_seconds' => 0,
                'focus_time_label' => '0m',
            ],
            'department_stats' => [],
            'productivity_distribution' => [
                ['label' => 'High Performers', 'value' => 0, 'pct' => 0, 'tone' => 'green'],
                ['label' => 'Average', 'value' => 0, 'pct' => 0, 'tone' => 'yellow'],
                ['label' => 'Needs Attention', 'value' => 0, 'pct' => 0, 'tone' => 'red'],
            ],
            'current_activity' => [
                ['label' => 'Working', 'value' => 0, 'tone' => 'green'],
                ['label' => 'Idle', 'value' => 0, 'tone' => 'orange'],
                ['label' => 'Paused', 'value' => 0, 'tone' => 'amber'],
                ['label' => 'Offline', 'value' => 0, 'tone' => 'gray'],
            ],
            'application_usage' => [],
            'attention_required' => [],
            'html' => [
                'team_health' => '',
                'attention_required' => '',
                'workforce_snapshot' => '',
                'workforce_analytics' => '',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array{online: int, idle: int, paused: int, offline: int}  $summary
     * @param  Collection<int, AgentDailySummary>  $todaySummaries
     * @param  Collection<int, AgentDailySummary>  $yesterdaySummaries
     * @return array<string, mixed>
     */
    private function buildDashboardPayload(
        int $companyId,
        Collection $employees,
        array $rows,
        array $summary,
        Collection $todaySummaries,
        Collection $yesterdaySummaries,
    ): array {
        $totalEmployees = max(1, $employees->count());
        $onlineCount = (int) ($summary['online'] ?? 0);
        $onlinePercentage = round(($onlineCount / $totalEmployees) * 100, 1);

        $todayScore = $rows !== [] ? round(collect($rows)->avg('score'), 1) : 0;
        $yesterdayScore = $yesterdaySummaries->isNotEmpty()
            ? round((float) $yesterdaySummaries->avg('avg_activity_pct'), 1)
            : 0;
        $yesterdayPresent = $yesterdaySummaries->filter(fn ($summary) => (int) ($summary->active_seconds ?? 0) > 0)->count();

        $focusTimeSeconds = (int) $todaySummaries->sum('active_seconds');
        $averageFocusSeconds = $todaySummaries->isNotEmpty()
            ? (int) round($focusTimeSeconds / max(1, $todaySummaries->count()))
            : 0;

        $attendancePresent = $todaySummaries->filter(fn ($summary) => (int) ($summary->active_seconds ?? 0) > 0)->count();
        $attendanceLate = (int) ($summary['idle'] ?? 0) + (int) ($summary['paused'] ?? 0);
        $attendanceAbsent = (int) ($summary['offline'] ?? 0);

        $departmentStats = $this->buildDepartmentStats($employees, $rows);
        $productivityDistribution = $this->buildProductivityDistribution($rows);
        $currentActivity = $this->buildCurrentActivity($summary);
        $attentionRequired = $this->buildAttentionRequired($rows);
        $applicationUsage = $this->buildApplicationUsage($rows);
        $teamHealthScore = $this->buildTeamHealthScore($onlinePercentage, $todayScore, count($attentionRequired));
        $teamHealthLabel = $this->labelTeamHealth($teamHealthScore);
        $activeApplications = collect($rows)
            ->pluck('active_app')
            ->filter()
            ->unique()
            ->count();

        return [
            'meta' => [
                'total_employees' => $employees->count(),
                'online_count' => $onlineCount,
                'online_percentage' => $onlinePercentage,
                'online_trend' => MonitorAnalyticsHelper::trend((float) $onlineCount, (float) $yesterdayPresent),
                'productivity_score' => $todayScore,
                'productivity_trend' => MonitorAnalyticsHelper::trend($todayScore, $yesterdayScore),
                'team_health_score' => $teamHealthScore,
                'team_health_label' => $teamHealthLabel,
                'active_applications' => $activeApplications,
                'attendance' => [
                    'present' => $attendancePresent,
                    'late' => $attendanceLate,
                    'absent' => $attendanceAbsent,
                ],
                'focus_time_seconds' => $focusTimeSeconds,
                'focus_time_label' => MonitorAnalyticsHelper::formatDuration($focusTimeSeconds),
                'average_focus_seconds' => $averageFocusSeconds,
                'average_focus_label' => MonitorAnalyticsHelper::formatDuration($averageFocusSeconds),
                'attention_count' => count($attentionRequired),
            ],
            'department_stats' => $departmentStats,
            'productivity_distribution' => $productivityDistribution,
            'current_activity' => $currentActivity,
            'application_usage' => $applicationUsage,
            'attention_required' => $attentionRequired,
        ];
    }

    /**
     * @param  Collection<int, User>  $employees
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildDepartmentStats(Collection $employees, array $rows): array
    {
        $byDepartment = $employees->groupBy(fn ($employee) => $employee->employeeDetail?->department_id ?? 0);
        $rowsByDepartment = collect($rows)->groupBy(fn ($row) => $row['department_id'] ?? 0);

        $stats = [];

        foreach ($byDepartment as $departmentId => $departmentEmployees) {
            $departmentName = $departmentEmployees->first()?->employeeDetail?->department?->team_name ?: 'Unassigned';
            $departmentRows = $rowsByDepartment->get($departmentId, collect());
            $onlineCount = (int) $departmentRows->whereIn('status', ['online', 'idle', 'paused'])->count();
            $offlineCount = (int) $departmentRows->where('status', 'offline')->count();
            $total = max(1, $departmentRows->count());
            $onlinePct = round(($onlineCount / $total) * 100, 1);
            $offlinePct = round(($offlineCount / $total) * 100, 1);
            $score = $departmentRows->isNotEmpty() ? round((float) $departmentRows->avg('score'), 1) : 0;

            $stats[] = [
                'id' => $departmentId,
                'name' => $departmentName,
                'online' => $onlineCount,
                'offline' => $offlineCount,
                'total' => $departmentRows->count(),
                'online_pct' => $onlinePct,
                'offline_pct' => $offlinePct,
                'score' => $score,
            ];
        }

        usort($stats, fn ($a, $b) => strcasecmp((string) $a['name'], (string) $b['name']));

        return $stats;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildProductivityDistribution(array $rows): array
    {
        $count = max(1, count($rows));
        $high = collect($rows)->filter(fn ($row) => (float) ($row['score'] ?? 0) >= 80)->count();
        $average = collect($rows)->filter(fn ($row) => (float) ($row['score'] ?? 0) >= 60 && (float) ($row['score'] ?? 0) < 80)->count();
        $needsAttention = max(0, count($rows) - $high - $average);

        return [
            [
                'label' => 'High Performers',
                'value' => $high,
                'pct' => round(($high / $count) * 100, 1),
                'tone' => 'green',
            ],
            [
                'label' => 'Average',
                'value' => $average,
                'pct' => round(($average / $count) * 100, 1),
                'tone' => 'yellow',
            ],
            [
                'label' => 'Needs Attention',
                'value' => $needsAttention,
                'pct' => round(($needsAttention / $count) * 100, 1),
                'tone' => 'red',
            ],
        ];
    }

    /**
     * @param  array{online: int, idle: int, paused: int, offline: int}  $summary
     * @return array<int, array<string, mixed>>
     */
    private function buildCurrentActivity(array $summary): array
    {
        $total = max(1, array_sum($summary));

        return [
            [
                'label' => 'Working',
                'value' => (int) ($summary['online'] ?? 0),
                'pct' => round(((int) ($summary['online'] ?? 0) / $total) * 100, 1),
                'tone' => 'green',
            ],
            [
                'label' => 'Idle',
                'value' => (int) ($summary['idle'] ?? 0),
                'pct' => round(((int) ($summary['idle'] ?? 0) / $total) * 100, 1),
                'tone' => 'orange',
            ],
            [
                'label' => 'Paused',
                'value' => (int) ($summary['paused'] ?? 0),
                'pct' => round(((int) ($summary['paused'] ?? 0) / $total) * 100, 1),
                'tone' => 'amber',
            ],
            [
                'label' => 'Offline',
                'value' => (int) ($summary['offline'] ?? 0),
                'pct' => round(((int) ($summary['offline'] ?? 0) / $total) * 100, 1),
                'tone' => 'gray',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildAttentionRequired(array $rows): array
    {
        $alerts = [];

        foreach ($rows as $row) {
            $status = $row['status'] ?? 'offline';
            $score = (float) ($row['score'] ?? 0);
            $reason = null;
            $tone = 'amber';

            if ($status === 'offline' && $score < 50) {
                $reason = 'Unusual inactivity';
                $tone = 'gray';
            } elseif ($status === 'paused') {
                $reason = 'Excessive pauses';
                $tone = 'amber';
            } elseif ($status === 'idle' && $score < 60) {
                $reason = 'Idle for 30+ minutes';
                $tone = 'orange';
            } elseif ($score < 50) {
                $reason = 'Low productivity score';
                $tone = 'red';
            }

            if (!$reason) {
                continue;
            }

            $severityLabel = match ($tone) {
                'red' => 'Critical',
                'orange' => 'High',
                'amber' => 'Medium',
                default => 'Low',
            };

            $alerts[] = [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'employee_code' => $row['employee_code'],
                'department' => $row['department'] ?: 'Unassigned',
                'avatar_url' => $row['avatar_url'] ?? null,
                'status' => $status,
                'score' => $score,
                'issue' => $reason,
                'reason' => $reason,
                'tone' => $tone,
                'severity_label' => $severityLabel,
                'active_app' => $row['active_app'] ?: 'No active app',
                'last_activity_label' => $row['last_activity_label'] ?? 'No recent activity',
                'view_url' => route('monitor.show', $row['user_id']),
            ];
        }

        usort($alerts, function (array $a, array $b): int {
            $priority = [
                'red' => 0,
                'orange' => 1,
                'amber' => 2,
                'gray' => 3,
            ];

            $toneCompare = ($priority[$a['tone']] ?? 9) <=> ($priority[$b['tone']] ?? 9);

            if ($toneCompare !== 0) {
                return $toneCompare;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return array_slice($alerts, 0, 4);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildApplicationUsage(array $rows): array
    {
        $usage = collect($rows)
            ->pluck('active_app')
            ->filter()
            ->countBy()
            ->sortDesc();
        $total = max(1, (int) $usage->sum());

        return $usage->take(4)->map(function ($count, $name) use ($total) {
            return [
                'label' => $name,
                'value' => $count,
                'pct' => round(($count / $total) * 100, 1),
            ];
        })->values()->all();
    }

    private function buildTeamHealthScore(float $onlinePercentage, float $productivityScore, int $attentionCount): float
    {
        $base = ($onlinePercentage * 0.45) + ($productivityScore * 0.55);
        $penalty = min(20, $attentionCount * 4);

        return round(max(0, min(100, $base - $penalty)), 0);
    }

    private function labelTeamHealth(float $score): string
    {
        if ($score >= 85) {
            return 'Excellent';
        }

        if ($score >= 70) {
            return 'Good';
        }

        if ($score >= 50) {
            return 'Needs Attention';
        }

        return 'Critical';
    }

    private function buildAppIcon(?string $label): array
    {
        $label = trim((string) $label);

        if ($label === '') {
            $label = 'App';
        }

        return app(LogoService::class)->letterAvatarMeta($label);
    }

    private function buildLastActivityLabel(string $status, ?Carbon $lastSeenAt): string
    {
        if ($status === 'online') {
            return 'Currently Active';
        }

        if (!$lastSeenAt) {
            return 'No recent activity';
        }

        return $lastSeenAt->timezone(company()->timezone)->diffForHumans(now(company()->timezone), true) . ' ago';
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, AgentDailySummary>
     */
    private function getDailySummaries(int $companyId, Collection $userIds, string $date): Collection
    {
        return AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');
    }

    private function getEmployees(int $companyId, ?int $departmentId): Collection
    {
        $query = $this->permissionScope->scopedEmployeeQuery($companyId);

        if ($departmentId) {
            $query->whereHas('employeeDetail', fn ($q) => $q->where('department_id', $departmentId));
        }

        EmployeeMonitoring::scopeEnabledEmployees($query);

        return $query->orderBy('name')->get()->load(['employeeDetail.department']);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, AgentHeartbeat>
     */
    private function getLatestHeartbeats(int $companyId, Collection $userIds): Collection
    {
        $latestSub = AgentHeartbeat::query()
            ->selectRaw('user_id, MAX(created_at) as last_at')
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        return AgentHeartbeat::query()
            ->where('company_id', $companyId)
            ->joinSub($latestSub, 'latest', function ($join) {
                $join->on('agent_heartbeats.user_id', '=', 'latest.user_id')
                    ->on('agent_heartbeats.created_at', '=', 'latest.last_at');
            })
            ->get()
            ->keyBy('user_id');
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<int, float>
     */
    private function getTodayScores(Collection $userIds): array
    {
        return AgentActivityWindow::query()
            ->selectRaw('user_id, AVG(activity_pct) as score')
            ->whereIn('user_id', $userIds)
            ->whereDate('window_start', today())
            ->where('is_idle', false)
            ->groupBy('user_id')
            ->pluck('score', 'user_id')
            ->map(fn ($score) => (float) $score)
            ->all();
    }

    private function resolveStatus(?AgentHeartbeat $heartbeat): string
    {
        if (!$heartbeat || $heartbeat->created_at->diffInMinutes(now()) >= self::ONLINE_THRESHOLD_MINUTES) {
            return 'offline';
        }

        if ($heartbeat->is_paused) {
            return 'paused';
        }

        if ($heartbeat->is_idle) {
            return 'idle';
        }

        return 'online';
    }
}
