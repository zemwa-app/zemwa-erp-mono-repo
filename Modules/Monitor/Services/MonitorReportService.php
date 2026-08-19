<?php

namespace Modules\Monitor\Services;

use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Monitor\Support\ListSearch;
use Modules\Monitor\Services\Analytics\LogoService;
use Modules\RestAPI\Entities\AgentActivityLog;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Entities\AgentScreenshot;

class MonitorReportService
{
    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }

    public const TAB_PRODUCTIVITY = 'productivity';

    public const TAB_APP_USAGE = 'app_usage';

    public const TAB_WEBSITE_USAGE = 'website_usage';

    public const TAB_IDLE = 'idle';

    public const TAB_SCREENSHOTS = 'screenshots';

    public const METRIC_PRODUCTIVITY = 'productivity';

    public const METRIC_ACTIVE_TIME = 'active_time';

    public const METRIC_IDLE_TIME = 'idle_time';

    public const METRIC_SCREENSHOTS = 'screenshots';

    /**
     * @return array<string, mixed>
     */
    public function parseFilters(Request $request): array
    {
        $timezone = company()->timezone;
        $defaultEnd = now($timezone)->toDateString();
        $defaultStart = now($timezone)->startOfWeek(Carbon::MONDAY)->toDateString();

        $employeeIds = collect($request->query('employee', []))
            ->filter(fn ($id) => $id !== null && $id !== '' && $id !== 'all')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $department = $request->query('department', 'all');
        $startDate = Carbon::parse($request->query('start_date', $defaultStart), $timezone)->toDateString();
        $endDate = Carbon::parse($request->query('end_date', $defaultEnd), $timezone)->toDateString();

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $metric = $request->query('metric', self::METRIC_PRODUCTIVITY);
        if (!in_array($metric, [self::METRIC_PRODUCTIVITY, self::METRIC_ACTIVE_TIME, self::METRIC_IDLE_TIME, self::METRIC_SCREENSHOTS], true)) {
            $metric = self::METRIC_PRODUCTIVITY;
        }

        $tab = $request->query('tab', self::TAB_PRODUCTIVITY);
        if (!in_array($tab, [self::TAB_PRODUCTIVITY, self::TAB_APP_USAGE, self::TAB_WEBSITE_USAGE, self::TAB_IDLE, self::TAB_SCREENSHOTS], true)) {
            $tab = self::TAB_PRODUCTIVITY;
        }

        return [
            'employee_ids' => $employeeIds,
            'department' => $department,
            'department_id' => $department !== 'all' ? (int) $department : null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'metric' => $metric,
            'tab' => $tab,
            'search' => ListSearch::normalize($request->query('search')),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function hasActiveFilters(array $filters): bool
    {
        $timezone = company()->timezone;
        $defaultEnd = now($timezone)->toDateString();
        $defaultStart = now($timezone)->startOfWeek(Carbon::MONDAY)->toDateString();

        return !empty($filters['employee_ids'])
            || $filters['department'] !== 'all'
            || $filters['metric'] !== self::METRIC_PRODUCTIVITY
            || $filters['start_date'] !== $defaultStart
            || $filters['end_date'] !== $defaultEnd
            || ($filters['search'] ?? '') !== '';
    }

    /**
     * @return array{employees: Collection, teams: Collection}
     */
    public function getFilterOptions(int $companyId): array
    {
        return [
            'employees' => $this->permissionScope->getEmployees($companyId, null, ['id', 'name', 'image']),
            'teams' => Team::where('company_id', $companyId)->orderBy('team_name')->get(['id', 'team_name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getReportData(int $companyId, array $filters): array
    {
        $employees = $this->resolveEmployees($companyId, $filters);
        $userIds = $employees->pluck('id');
        $workWeek = $this->resolveWorkWeek($filters['start_date'], $filters['end_date']);
        $rangeStart = Carbon::parse($filters['start_date'])->startOfDay();
        $rangeEnd = Carbon::parse($filters['end_date'])->endOfDay();

        $search = ListSearch::normalize($filters['search'] ?? '');

        return match ($filters['tab']) {
            self::TAB_APP_USAGE => [
                'tab' => self::TAB_APP_USAGE,
                'rows' => ListSearch::filterRows(
                    $this->getAppUsageRows($userIds, $rangeStart, $rangeEnd, appsOnly: true),
                    $search,
                    ['employee', 'app_name']
                ),
            ],
            self::TAB_WEBSITE_USAGE => [
                'tab' => self::TAB_WEBSITE_USAGE,
                'rows' => ListSearch::filterRows(
                    $this->getAppUsageRows($userIds, $rangeStart, $rangeEnd, websitesOnly: true),
                    $search,
                    ['employee', 'app_name']
                ),
            ],
            self::TAB_IDLE => [
                'tab' => self::TAB_IDLE,
                'rows' => ListSearch::filterRows(
                    $this->getIdleAnalysisRows($userIds, $rangeStart, $rangeEnd),
                    $search,
                    ['employee']
                ),
            ],
            self::TAB_SCREENSHOTS => [
                'tab' => self::TAB_SCREENSHOTS,
                'rows' => ListSearch::filterRows(
                    $this->getScreenshotsSummaryRows($userIds, $rangeStart, $rangeEnd),
                    $search,
                    ['employee']
                ),
            ],
            default => [
                'tab' => self::TAB_PRODUCTIVITY,
                'work_week' => $workWeek,
                'metric' => $filters['metric'],
                'rows' => ListSearch::filterRows(
                    $this->getProductivitySummaryRows($employees, $userIds, $workWeek, $filters['metric']),
                    $search,
                    ['name', 'department']
                ),
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, User>
     */
    private function resolveEmployees(int $companyId, array $filters): Collection
    {
        $query = $this->permissionScope->scopedEmployeeQuery($companyId)
            ->with('employeeDetail.department')
            ->orderBy('name');

        if (!empty($filters['employee_ids'])) {
            $query->whereIn('id', $filters['employee_ids']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employeeDetail', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        return $query->get();
    }

    /**
     * @return array<int, array{key: string, label: string, date: string, short: string}>
     */
    public function resolveWorkWeek(string $startDate, string $endDate): array
    {
        $anchor = Carbon::parse($endDate);
        $monday = $anchor->copy()->startOfWeek(Carbon::MONDAY);
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $keys = ['mon', 'tue', 'wed', 'thu', 'fri'];
        $days = [];

        for ($i = 0; $i < 5; $i++) {
            $day = $monday->copy()->addDays($i);
            $days[] = [
                'key' => $keys[$i],
                'label' => $labels[$i],
                'date' => $day->toDateString(),
                'short' => $day->format('M j'),
            ];
        }

        return $days;
    }

    /**
     * @param  Collection<int, User>  $employees
     * @param  Collection<int, int>  $userIds
     * @param  array<int, array{key: string, label: string, date: string, short: string}>  $workWeek
     * @return array<int, array<string, mixed>>
     */
    private function getProductivitySummaryRows(Collection $employees, Collection $userIds, array $workWeek, string $metric): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        $weekDates = collect($workWeek)->pluck('date');
        $rangeStart = Carbon::parse($weekDates->first())->timezone(company()->timezone);
        $rangeEnd = Carbon::parse($weekDates->last())->timezone(company()->timezone)->addDay()->subSecond();

        $windows = AgentActivityWindow::query()
            ->whereIn('user_id', $userIds)
            ->where('window_start', '>=', $rangeStart->toDateTimeString())
            ->where('window_start', '<=', $rangeEnd->toDateTimeString())
            ->get()
            ->groupBy('user_id');

        $screenshots = AgentScreenshot::query()
            ->whereIn('user_id', $userIds)
            ->where('captured_at', '>=', $rangeStart->toDateTimeString())
            ->where('captured_at', '<=', $rangeEnd->toDateTimeString())
            ->get()
            ->groupBy('user_id');

        $rows = [];

        foreach ($employees as $employee) {
            $userWindows = $windows->get($employee->id, collect());
            $userScreenshots = $screenshots->get($employee->id, collect());
            $dayCells = [];
            $numericValues = [];

            foreach ($workWeek as $day) {
                $cell = $this->buildMetricCell(
                    $metric,
                    $userWindows->filter(fn ($w) => $w->window_start->format('Y-m-d') === $day['date']),
                    $userScreenshots->filter(fn ($s) => $s->captured_at->format('Y-m-d') === $day['date'])
                );
                $dayCells[$day['key']] = $cell;
                if ($cell['value'] !== null) {
                    $numericValues[] = $cell['value'];
                }
            }

            $avgCell = $this->buildAverageCell($metric, $numericValues);
            $sparkline = collect($workWeek)->map(fn ($d) => $dayCells[$d['key']]['value'] ?? 0)->all();

            $rows[] = [
                'user_id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->employeeDetail?->department?->team_name ?? '—',
                'days' => $dayCells,
                'avg' => $avgCell,
                'trend' => $this->buildTrend($sparkline),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, AgentActivityWindow>  $dayWindows
     * @param  Collection<int, AgentScreenshot>  $dayScreenshots
     * @return array{value: ?float, display: string, class: string}
     */
    private function buildMetricCell(string $metric, Collection $dayWindows, Collection $dayScreenshots): array
    {
        $value = match ($metric) {
            self::METRIC_ACTIVE_TIME => $this->sumActiveSeconds($dayWindows),
            self::METRIC_IDLE_TIME => $this->sumIdleSeconds($dayWindows),
            self::METRIC_SCREENSHOTS => (float) $dayScreenshots->count(),
            default => $this->dailyProductivityScore($dayWindows),
        };

        if ($value === null) {
            return [
                'value' => null,
                'display' => '—',
                'class' => 'bg-gray-50 text-gray-400',
            ];
        }

        return match ($metric) {
            self::METRIC_ACTIVE_TIME, self::METRIC_IDLE_TIME => [
                'value' => $value,
                'display' => MonitorEmployeeDetailService::formatDuration((int) $value),
                'class' => 'bg-slate-50 text-slate-800',
            ],
            self::METRIC_SCREENSHOTS => [
                'value' => $value,
                'display' => (string) (int) $value,
                'class' => 'bg-indigo-50 text-indigo-800',
            ],
            default => [
                'value' => $value,
                'display' => number_format($value, 1) . '%',
                'class' => self::scoreCellClass($value),
            ],
        };
    }

    /**
     * @param  array<int, float>  $values
     * @return array{value: ?float, display: string, class: string}
     */
    private function buildAverageCell(string $metric, array $values): array
    {
        if ($values === []) {
            return [
                'value' => null,
                'display' => '—',
                'class' => 'bg-gray-50 text-gray-400',
            ];
        }

        $avg = array_sum($values) / count($values);

        return match ($metric) {
            self::METRIC_ACTIVE_TIME, self::METRIC_IDLE_TIME => [
                'value' => $avg,
                'display' => MonitorEmployeeDetailService::formatDuration((int) round($avg)),
                'class' => 'bg-slate-100 text-slate-900 font-semibold',
            ],
            self::METRIC_SCREENSHOTS => [
                'value' => $avg,
                'display' => number_format($avg, 1),
                'class' => 'bg-indigo-100 text-indigo-900 font-semibold',
            ],
            default => [
                'value' => round($avg, 1),
                'display' => number_format($avg, 1) . '%',
                'class' => self::scoreCellClass($avg) . ' font-semibold',
            ],
        };
    }

    /**
     * @param  Collection<int, AgentActivityWindow>  $dayWindows
     */
    private function dailyProductivityScore(Collection $dayWindows): ?float
    {
        $nonIdle = $dayWindows->where('is_idle', false);

        if ($nonIdle->isEmpty()) {
            return null;
        }

        return round((float) $nonIdle->avg('activity_pct'), 1);
    }

    /**
     * @param  Collection<int, AgentActivityWindow>  $dayWindows
     */
    private function sumActiveSeconds(Collection $dayWindows): ?float
    {
        $seconds = (int) $dayWindows->where('is_idle', false)
            ->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));

        return $seconds > 0 ? (float) $seconds : null;
    }

    /**
     * @param  Collection<int, AgentActivityWindow>  $dayWindows
     */
    private function sumIdleSeconds(Collection $dayWindows): ?float
    {
        $seconds = (int) $dayWindows->where('is_idle', true)
            ->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));

        return $seconds > 0 ? (float) $seconds : null;
    }

    /**
     * @param  array<int, float>  $values
     * @return array{direction: string, icon: string, class: string, sparkline: array<int, float>}
     */
    private function buildTrend(array $values): array
    {
        $filled = array_values(array_filter($values, fn ($v) => $v > 0));
        $direction = 'flat';
        $icon = 'minus';
        $class = 'text-gray-500';

        if (count($filled) >= 2) {
            $firstHalf = array_slice($filled, 0, (int) ceil(count($filled) / 2));
            $secondHalf = array_slice($filled, (int) floor(count($filled) / 2));
            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);
            $delta = $secondAvg - $firstAvg;

            if ($delta > 2) {
                $direction = 'up';
                $icon = 'arrow-up';
                $class = 'text-green-600';
            } elseif ($delta < -2) {
                $direction = 'down';
                $icon = 'arrow-down';
                $class = 'text-red-600';
            }
        }

        $max = max($values ?: [1]);
        $sparkline = array_map(fn ($v) => $max > 0 ? (int) round(($v / $max) * 100) : 0, $values);

        return [
            'direction' => $direction,
            'icon' => $icon,
            'class' => $class,
            'sparkline' => $sparkline,
        ];
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<int, array<string, mixed>>
     */
    private function getAppUsageRows(
        Collection $userIds,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        bool $appsOnly = false,
        bool $websitesOnly = false,
    ): array {
        if ($userIds->isEmpty()) {
            return [];
        }

        $employees = User::whereIn('id', $userIds)->get(['id', 'name'])->keyBy('id');

        $logs = AgentActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->where(function ($q) use ($rangeStart, $rangeEnd) {
                $q->whereBetween('started_at', [$rangeStart->timezone(company()->timezone)->toDateTimeString(), $rangeEnd->timezone(company()->timezone)->toDateTimeString()])
                    ->orWhereBetween('ended_at', [$rangeStart->timezone(company()->timezone)->toDateTimeString(), $rangeEnd->timezone(company()->timezone)->toDateTimeString()]);
            })
            ->get();

        $logoService = app(LogoService::class);

        if ($websitesOnly) {
            $logs = $logs->filter(fn ($log) => $log->url && trim($log->url) !== '')->values();
        } elseif ($appsOnly) {
            $logs = $logs->filter(fn ($log) => !$log->url || trim($log->url) === '')->values();
        }

        $grouped = $logs->groupBy(function ($log) use ($logoService) {
            $pattern = $this->usagePatternForLog($log, $logoService);

            return $log->user_id . '|' . $pattern;
        });
        $rows = [];

        foreach ($grouped as $key => $appLogs) {
            [$userId, $pattern] = explode('|', $key, 2);
            $totalSeconds = (int) $appLogs->sum('duration_seconds');
            $primary = $appLogs->sortByDesc('duration_seconds')->first();
            $icons = $logoService->resolveForActivityLog($primary->url, $primary->app_name, $primary->process_name);
            $isUrl = (bool) ($primary->url && trim($primary->url) !== '');
            $rows[] = [
                'user_id' => (int) $userId,
                'employee' => $employees->get($userId)?->name ?? '—',
                'app_name' => $pattern,
                'type' => $isUrl ? 'url' : 'app',
                'category' => $primary->category,
                'subcategory' => $primary->subcategory,
                'icon_url' => $icons['icon_url'],
                'letter_avatar' => $icons['letter_avatar'],
                'duration_seconds' => $totalSeconds,
                'duration_label' => MonitorEmployeeDetailService::formatDuration($totalSeconds),
            ];
        }

        $grandTotal = max(array_sum(array_column($rows, 'duration_seconds')), 1);

        return collect($rows)
            ->map(function (array $row) use ($grandTotal) {
                $row['pct'] = round(($row['duration_seconds'] / $grandTotal) * 100, 1);

                return $row;
            })
            ->sortByDesc('duration_seconds')
            ->values()
            ->all();
    }

    private function usagePatternForLog(AgentActivityLog $log, LogoService $logoService): string
    {
        if ($log->url && trim($log->url) !== '') {
            return $logoService->extractDomain($log->url) ?? 'unknown';
        }

        return $log->process_name ?: ($log->app_name ?: 'Unknown');
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<int, array<string, mixed>>
     */
    private function getIdleAnalysisRows(Collection $userIds, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        $employees = User::whereIn('id', $userIds)->get(['id', 'name'])->keyBy('id');

        $windows = AgentActivityWindow::query()
            ->whereIn('user_id', $userIds)
            ->where('window_start', '>=', $rangeStart->timezone(company()->timezone)->toDateTimeString())
            ->where('window_start', '<=', $rangeEnd->timezone(company()->timezone)->toDateTimeString())
            ->get()
            ->groupBy(fn ($w) => $w->user_id . '|' . $w->window_start->format('Y-m-d'));

        $rows = [];

        foreach ($windows as $key => $dayWindows) {
            [$userId, $date] = explode('|', $key, 2);
            $totalSeconds = (int) $dayWindows->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
            $idleSeconds = (int) $dayWindows->where('is_idle', true)
                ->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
            $longestIdle = (int) $dayWindows->where('is_idle', true)
                ->max(fn ($w) => $w->window_start->diffInSeconds($w->window_end)) ?? 0;

            $rows[] = [
                'employee' => $employees->get((int) $userId)?->name ?? '—',
                'date' => Carbon::parse($date)->format(company()->date_format),
                'idle_seconds' => $idleSeconds,
                'idle_label' => MonitorEmployeeDetailService::formatDuration($idleSeconds),
                'idle_pct' => $totalSeconds > 0 ? round(($idleSeconds / $totalSeconds) * 100, 1) : 0,
                'longest_idle' => MonitorEmployeeDetailService::formatDuration($longestIdle),
            ];
        }

        return collect($rows)->sortBy(['employee', 'date'])->values()->all();
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<int, array<string, mixed>>
     */
    private function getScreenshotsSummaryRows(Collection $userIds, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        $employees = User::whereIn('id', $userIds)->get(['id', 'name'])->keyBy('id');

        $shots = AgentScreenshot::query()
            ->whereIn('user_id', $userIds)
            ->where('captured_at', '>=', $rangeStart->timezone(company()->timezone)->toDateTimeString())
            ->where('captured_at', '<=', $rangeEnd->timezone(company()->timezone)->toDateTimeString())
            ->get()
            ->groupBy('user_id');

        $rows = [];

        foreach ($userIds as $userId) {
            $userShots = $shots->get($userId, collect());
            $rows[] = [
                'employee' => $employees->get($userId)?->name ?? '—',
                'total' => $userShots->count(),
                'productive' => $userShots->where('category', 'productive')->count(),
                'neutral' => $userShots->where('category', 'neutral')->count(),
                'unproductive' => $userShots->where('category', 'unproductive')->count(),
            ];
        }

        return collect($rows)->sortByDesc('total')->values()->all();
    }

    public static function scoreCellClass(float $score): string
    {
        if ($score >= 80) {
            return 'bg-green-100 text-green-800';
        }

        if ($score >= 60) {
            return 'bg-yellow-100 text-yellow-800';
        }

        return 'bg-red-100 text-red-800';
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $reportData
     * @return array<int, array<int, string|float|null>>
     */
    public function buildExportRows(array $filters, array $reportData): array
    {
        return match ($reportData['tab']) {
            self::TAB_APP_USAGE, self::TAB_WEBSITE_USAGE => collect($reportData['rows'])->map(fn ($r) => [
                $r['employee'],
                $r['app_name'],
                $r['category'] ?? '',
                $r['subcategory'] ?? '',
                $r['duration_label'],
                $r['pct'] . '%',
            ])->all(),
            self::TAB_IDLE => collect($reportData['rows'])->map(fn ($r) => [
                $r['employee'],
                $r['date'],
                $r['idle_label'],
                $r['idle_pct'] . '%',
                $r['longest_idle'],
            ])->all(),
            self::TAB_SCREENSHOTS => collect($reportData['rows'])->map(fn ($r) => [
                $r['employee'],
                $r['total'],
                $r['productive'],
                $r['neutral'],
                $r['unproductive'],
            ])->all(),
            default => collect($reportData['rows'])->map(function ($r) use ($reportData) {
                $row = [$r['name'], $r['department']];
                foreach ($reportData['work_week'] as $day) {
                    $row[] = $r['days'][$day['key']]['display'] ?? '—';
                }
                $row[] = $r['avg']['display'];
                $row[] = $r['trend']['direction'];

                return $row;
            })->all(),
        };
    }

    /**
     * @param  array<string, mixed>  $reportData
     * @return array<int, string>
     */
    public function buildExportHeadings(array $reportData): array
    {
        return match ($reportData['tab']) {
            self::TAB_APP_USAGE => [
                __('app.employee'),
                __('monitor::app.appName'),
                __('app.category'),
                __('monitor::app.subcategory'),
                __('app.duration'),
                __('monitor::app.pctOfTotal'),
            ],
            self::TAB_WEBSITE_USAGE => [
                __('app.employee'),
                __('monitor::app.domain'),
                __('app.category'),
                __('monitor::app.subcategory'),
                __('app.duration'),
                __('monitor::app.pctOfTotal'),
            ],
            self::TAB_IDLE => [
                __('app.employee'),
                __('app.date'),
                __('monitor::app.idleTime'),
                __('monitor::app.idlePct'),
                __('monitor::app.longestIdle'),
            ],
            self::TAB_SCREENSHOTS => [
                __('app.employee'),
                __('monitor::app.totalScreenshots'),
                __('monitor::app.categoryProductive'),
                __('monitor::app.categoryNeutral'),
                __('monitor::app.categoryUnproductive'),
            ],
            default => array_merge(
                [__('app.employee'), __('app.menu.teams')],
                collect($reportData['work_week'])->pluck('label')->all(),
                [__('monitor::app.avg'), __('monitor::app.trend')]
            ),
        };
    }
}
