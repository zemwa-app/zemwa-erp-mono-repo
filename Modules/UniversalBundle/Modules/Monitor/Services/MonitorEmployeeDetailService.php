<?php

namespace Modules\Monitor\Services;

use App\Models\EmployeeDetails;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\Monitor\Entities\AgentDailySummary;
use Modules\Monitor\Entities\AgentSession;
use Modules\Monitor\Services\Analytics\LogoService;
use Modules\RestAPI\Entities\AgentActivityLog;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Entities\AgentEvent;
use Modules\RestAPI\Entities\AgentNetworkLog;
use Modules\RestAPI\Entities\AgentHeartbeat;
use Modules\RestAPI\Entities\AgentScreenshot;

class MonitorEmployeeDetailService
{
    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }

    public function resolveEmployee(int $userId, int $companyId): User
    {
        $this->permissionScope->authorizeEmployee($userId, $companyId);

        $employee = User::with('employeeDetail.department')
            ->where('id', $userId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return $employee;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOverview(int $userId, Carbon $date): array
    {
        $cacheKey = sprintf(
            'monitor:overview:%d:%d:%s',
            company()->id,
            $userId,
            $date->toDateString()
        );

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $employee = $this->resolveEmployee($userId, company()->id);
        $companyId = (int) $employee->company_id;
        $timezone = company()->timezone;
        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), $timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
        $dayStartUtc = $dayStart->copy()->setTimezone('UTC');
        $dayEndUtc = $dayEnd->copy()->setTimezone('UTC');

        $windows = AgentActivityWindow::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('window_start', '>=', $dayStartUtc->toDateTimeString())
            ->where('window_start', '<=', $dayEndUtc->toDateTimeString())
            ->get();

        $logs = AgentActivityLog::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where(function ($q) use ($dayStartUtc, $dayEndUtc) {
                $q->whereBetween('started_at', [$dayStartUtc, $dayEndUtc])
                    ->orWhereBetween('ended_at', [$dayStartUtc, $dayEndUtc]);
            })
            ->orderBy('started_at')
            ->get();

        $nonIdle = $windows->where('is_idle', false);
        $score = $nonIdle->isNotEmpty() ? round($nonIdle->avg('activity_pct'), 1) : 0;
        $teamAverageScore = $this->getTeamAverageScore($companyId, $date);
        $scoreDelta = $teamAverageScore > 0 ? round($score - $teamAverageScore, 1) : 0;
        $scoreDeltaPct = $teamAverageScore > 0 ? round(($scoreDelta / max($teamAverageScore, 1)) * 100, 1) : 0;

        $activeSeconds = (int) $nonIdle->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
        $idleSeconds = (int) $windows->where('is_idle', true)->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
        $focusSeconds = $activeSeconds;

        $screenshotCount = AgentScreenshot::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('captured_at', '>=', $dayStartUtc->toDateTimeString())
            ->where('captured_at', '<=', $dayEndUtc->toDateTimeString())
            ->count();

        $logoService = app(LogoService::class);
        $topApps = $logs
            ->groupBy('app_name')
            ->map(function (Collection $logs, string $appName) use ($logoService) {
                $primary = $logs->sortByDesc('duration_seconds')->first();
                $totalSeconds = $logs->sum('duration_seconds');
                $icons = $logoService->resolveForActivityLog(
                    $primary->url,
                    $primary->app_name,
                    $primary->process_name
                );

                return [
                    'app_name' => $appName,
                    'category' => $primary->category,
                    'icon_url' => $icons['icon_url'],
                    'letter_avatar' => $icons['letter_avatar'],
                    'total_seconds' => (int) $totalSeconds,
                    'label' => self::formatDuration((int) $totalSeconds),
                ];
            })
            ->sortByDesc('total_seconds')
            ->values()
            ->take(8);

        $maxSeconds = max($topApps->max('total_seconds') ?? 1, 1);
        $topApps = $topApps->map(function (array $app) use ($maxSeconds) {
            $app['bar_pct'] = round(($app['total_seconds'] / $maxSeconds) * 100);

            return $app;
        });

        $keystrokes = (int) $windows->sum('keystrokes');
        $mouseClicks = (int) $windows->sum('mouse_clicks');
        $mouseDistance = (int) $windows->sum('mouse_distance');
        $scrollEvents = (int) $windows->sum('scroll_events');
        $session = AgentSession::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->first();
        $heartbeat = $this->getLatestHeartbeat($companyId, $userId);
        $status = $this->resolveDetailStatus($session, $heartbeat);
        $lastActivityAt = $this->resolveLastActivityAt($logs, $windows, $heartbeat, $session);
        $currentApp = $this->resolveCurrentApp($session, $heartbeat, $logs);
        $currentSessionDurationSeconds = $this->resolveCurrentSessionDurationSeconds($status, $session, $heartbeat, $lastActivityAt);
        $appLogs = $logs->filter(fn (AgentActivityLog $log) => !$log->url || trim((string) $log->url) === '');
        $timeline = $this->buildApplicationTimeline($appLogs);
        $workDistribution = $this->buildWorkDistribution($logs, $activeSeconds, $idleSeconds);
        $attentionInsights = $this->buildAttentionInsights($score, $teamAverageScore, $idleSeconds, $logs, $windows);
        $positiveInsights = $this->buildPositiveInsights($score, $teamAverageScore, $idleSeconds, $focusSeconds, $screenshotCount);
        $trend7 = $this->buildTrendSeries($userId, $companyId, 7);
        $trend30 = $this->buildTrendSeries($userId, $companyId, 30);
        $trendComparison = $this->buildTrendComparison($userId, $companyId);
        $managerSummary = $this->buildManagerSummary($employee, $score, $teamAverageScore, $topApps, $activeSeconds, $idleSeconds, $attentionInsights, $currentApp);
        [$productivityStatusLabel, $productivityStatusTone] = $this->resolveProductivityStatus($score);

        $overview = [
            'employee' => $employee,
            'active_seconds' => $activeSeconds,
            'active_label' => self::formatDuration($activeSeconds),
            'idle_seconds' => $idleSeconds,
            'idle_label' => self::formatDuration($idleSeconds),
            'focus_seconds' => $focusSeconds,
            'focus_label' => self::formatDuration($focusSeconds),
            'productivity_score' => $score,
            'productivity_status_label' => $productivityStatusLabel,
            'productivity_status_tone' => $productivityStatusTone,
            'team_average_score' => $teamAverageScore,
            'score_delta' => $scoreDelta,
            'score_delta_pct' => $scoreDeltaPct,
            'status' => $status,
            'status_label' => ucfirst($status),
            'status_tone' => $this->statusTone($status),
            'current_app' => $currentApp,
            'current_session_duration_seconds' => $currentSessionDurationSeconds,
            'current_session_duration_label' => self::formatDuration($currentSessionDurationSeconds),
            'last_activity_label' => $this->lastActivityLabel($lastActivityAt),
            'last_activity_at' => $lastActivityAt?->toIso8601String(),
            'screenshot_count' => $screenshotCount,
            'keystrokes' => self::formatCount($keystrokes),
            'mouse_clicks' => self::formatCount($mouseClicks),
            'mouse_distance' => self::formatCount($mouseDistance) . ' px',
            'scroll_events' => self::formatCount($scrollEvents),
            'top_apps' => $topApps,
            'timeline' => $timeline,
            'positive_insights' => $positiveInsights,
            'attention_insights' => $attentionInsights,
            'work_distribution' => $workDistribution,
            'trend_7' => $trend7,
            'trend_30' => $trend30,
            'trend_comparison' => $trendComparison,
            'manager_summary' => $managerSummary,
        ];

        Cache::put($cacheKey, $overview, now()->addMinutes(10));

        return $overview;
    }

    private function getTeamAverageScore(int $companyId, Carbon $date): float
    {
        return (float) AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereDate('date', $date->toDateString())
            ->avg('avg_activity_pct') ?: 0;
    }

    private function getLatestHeartbeat(int $companyId, int $userId): ?AgentHeartbeat
    {
        return AgentHeartbeat::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();
    }

    private function resolveDetailStatus(?AgentSession $session, ?AgentHeartbeat $heartbeat): string
    {
        if ($session?->is_online) {
            if ($heartbeat?->is_paused) {
                return 'paused';
            }

            if ($heartbeat?->is_idle) {
                return 'idle';
            }

            return 'online';
        }

        if ($heartbeat && $heartbeat->created_at->diffInMinutes(now()) < 2) {
            if ($heartbeat->is_paused) {
                return 'paused';
            }

            if ($heartbeat->is_idle) {
                return 'idle';
            }

            return 'online';
        }

        return 'offline';
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            'online' => 'green',
            'idle' => 'orange',
            'paused' => 'amber',
            default => 'gray',
        };
    }

    private function resolveCurrentApp(?AgentSession $session, ?AgentHeartbeat $heartbeat, Collection $logs): ?string
    {
        $ongoingLog = $logs->whereNull('ended_at')->sortByDesc('started_at')->first();

        return $ongoingLog?->app_name
            ?: $session?->active_app
            ?: $heartbeat?->active_app
            ?: $logs->sortByDesc('started_at')->first()?->app_name;
    }

    private function resolveLastActivityAt(Collection $logs, Collection $windows, ?AgentHeartbeat $heartbeat, ?AgentSession $session): ?Carbon
    {
        $latestLog = $logs->sortByDesc(fn (AgentActivityLog $log) => $log->ended_at ?? $log->started_at)->first();
        $latestWindow = $windows->sortByDesc('window_end')->first();
        $candidates = array_filter([
            $latestLog?->ended_at ?? $latestLog?->started_at,
            $latestWindow?->window_end ?? $latestWindow?->window_start,
            $heartbeat?->created_at,
            $session?->last_seen_at,
        ]);

        $latest = null;
        foreach ($candidates as $candidate) {
            if (!$candidate instanceof Carbon) {
                continue;
            }

            if (!$latest || $candidate->gt($latest)) {
                $latest = $candidate;
            }
        }

        return $latest;
    }

    private function resolveCurrentSessionDurationSeconds(string $status, ?AgentSession $session, ?AgentHeartbeat $heartbeat, ?Carbon $lastActivityAt): int
    {
        if (!in_array($status, ['online', 'idle', 'paused'], true)) {
            return 0;
        }

        $reference = $session?->last_seen_at ?: $heartbeat?->created_at ?: $lastActivityAt;

        return $reference instanceof Carbon ? max(0, $reference->diffInSeconds(now())) : 0;
    }

    private function lastActivityLabel(?Carbon $lastActivityAt): string
    {
        if (!$lastActivityAt) {
            return 'No recent activity';
        }

        $diff = $lastActivityAt->diffInMinutes(now());

        if ($diff < 1) {
            return 'Currently active';
        }

        return $lastActivityAt->diffForHumans(now(), true) . ' ago';
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @param  Collection<int, AgentActivityWindow>  $windows
     * @return array<int, array<string, mixed>>
     */
    private function buildActivityTimeline(Collection $logs, Collection $windows, Carbon $dayStart): array
    {
        $events = [];
        $timeFormat = company()->time_format;
        $recentLogs = $logs->sortBy('started_at')->values();

        $firstLog = $recentLogs->first();
        if ($firstLog) {
            $events[] = [
                'time' => $firstLog->started_at->timezone(company()->timezone)->format($timeFormat),
                'title' => 'Started work',
                'detail' => $firstLog->app_name ?: 'Work session began',
                'tone' => 'green',
                'icon' => 'play-circle',
                'sort_at' => $firstLog->started_at->timestamp,
            ];
        }

        $uniqueAppLogs = $recentLogs->groupBy(fn (AgentActivityLog $log) => $log->app_name ?: 'Unknown')
            ->map(fn (Collection $group) => $group->sortBy('started_at')->first())
            ->sortBy('started_at')
            ->take(4);

        foreach ($uniqueAppLogs as $log) {
            if (!$log || $firstLog?->id === $log->id) {
                continue;
            }

            $events[] = [
                'time' => $log->started_at->timezone(company()->timezone)->format($timeFormat),
                'title' => 'Opened ' . ($log->app_name ?: 'App'),
                'detail' => $log->window_title ?: ($log->process_name ?: 'Working session'),
                'tone' => 'gray',
                'icon' => 'window-maximize',
                'sort_at' => $log->started_at->timestamp,
            ];
        }

        foreach ($windows->where('is_idle', true)->sortBy('window_start')->take(3) as $window) {
            $duration = $window->window_start->diffInMinutes($window->window_end);

            if ($duration < 10) {
                continue;
            }

            $events[] = [
                'time' => $window->window_start->timezone(company()->timezone)->format($timeFormat),
                'title' => 'Idle for ' . $duration . ' minutes',
                'detail' => 'Activity paused',
                'tone' => 'amber',
                'icon' => 'pause-circle',
                'sort_at' => $window->window_start->timestamp,
            ];

            $events[] = [
                'time' => $window->window_end->timezone(company()->timezone)->format($timeFormat),
                'title' => 'Returned active',
                'detail' => 'Work resumed',
                'tone' => 'green',
                'icon' => 'arrow-right',
                'sort_at' => $window->window_end->timestamp,
            ];
        }

        $lastLog = $recentLogs->sortByDesc('started_at')->first();
        if ($lastLog) {
            $events[] = [
                'time' => ($lastLog->ended_at ?? $lastLog->started_at)->timezone(company()->timezone)->format($timeFormat),
                'title' => 'Latest activity',
                'detail' => $lastLog->app_name ?: 'Current work stream',
                'tone' => 'gray',
                'icon' => 'clock',
                'sort_at' => ($lastLog->ended_at ?? $lastLog->started_at)->timestamp,
            ];
        }

        usort($events, function (array $a, array $b) {
            return ($a['sort_at'] ?? 0) <=> ($b['sort_at'] ?? 0);
        });

        $events = array_map(function (array $event) {
            unset($event['sort_at']);

            return $event;
        }, $events);

        return array_slice($events, 0, 8);
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @return array<int, array<string, mixed>>
     */
    private function buildWorkDistribution(Collection $logs, int $activeSeconds, int $idleSeconds): array
    {
        $buckets = [
            'Development' => 0,
            'Communication' => 0,
            'Documentation' => 0,
            'Research' => 0,
            'Meetings' => 0,
            'Other' => 0,
        ];

        foreach ($logs as $log) {
            $bucket = $this->classifyWorkBucket($log);
            $buckets[$bucket] += max(0, (int) $log->duration_seconds);
        }

        $total = max(1, array_sum($buckets));

        return collect($buckets)->map(function (int $seconds, string $label) use ($total) {
            return [
                'label' => $label,
                'seconds' => $seconds,
                'label_seconds' => self::formatDuration($seconds),
                'pct' => round(($seconds / $total) * 100, 1),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @param  Collection<int, AgentActivityWindow>  $windows
     * @return array<int, array<string, mixed>>
     */
    private function buildAttentionInsights(float $score, float $teamAverageScore, int $idleSeconds, Collection $logs, Collection $windows): array
    {
        $insights = [];
        $uniqueApps = $logs->pluck('app_name')->filter()->unique()->count();
        $idleMinutes = (int) round($idleSeconds / 60);

        if ($score >= $teamAverageScore + 10) {
            $insights[] = ['label' => 'Productivity 10%+ above team average', 'tone' => 'green'];
        } elseif ($score >= $teamAverageScore) {
            $insights[] = ['label' => 'Productivity is above the team average', 'tone' => 'green'];
        }

        if ($idleMinutes < 20) {
            $insights[] = ['label' => 'Low idle time today', 'tone' => 'green'];
        }

        if ($uniqueApps >= 5) {
            $insights[] = ['label' => 'Balanced application mix across the day', 'tone' => 'green'];
        }

        $attention = [];

        if ($idleMinutes >= 20) {
            $attention[] = ['label' => 'Idle session exceeded 20 minutes', 'tone' => 'amber'];
        }

        if ($score < max(50, $teamAverageScore - 10)) {
            $attention[] = ['label' => 'Productivity below threshold', 'tone' => 'amber'];
        }

        if ($uniqueApps >= 10) {
            $attention[] = ['label' => 'Significant context switching', 'tone' => 'amber'];
        }

        if ($windows->where('is_idle', true)->count() >= 3) {
            $attention[] = ['label' => 'Multiple idle breaks recorded', 'tone' => 'amber'];
        }

        return [
            'positive' => array_slice($insights, 0, 3),
            'attention' => array_slice($attention, 0, 3),
        ];
    }

    private function buildPositiveInsights(float $score, float $teamAverageScore, int $idleSeconds, int $focusSeconds, int $screenshotCount): array
    {
        $items = [];
        $idleMinutes = (int) round($idleSeconds / 60);
        $focusMinutes = (int) round($focusSeconds / 60);

        if ($score >= $teamAverageScore + 10) {
            $items[] = ['label' => 'Productivity ' . number_format($score - $teamAverageScore, 1) . '% above team average', 'tone' => 'green'];
        }

        if ($focusMinutes >= 120) {
            $items[] = ['label' => 'Focus sessions longer than average', 'tone' => 'green'];
        }

        if ($idleMinutes < 20) {
            $items[] = ['label' => 'Low idle time', 'tone' => 'green'];
        }

        if ($screenshotCount > 0) {
            $items[] = ['label' => $screenshotCount . ' screenshots captured for activity context', 'tone' => 'green'];
        }

        return array_slice($items, 0, 4);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTrendSeries(int $userId, int $companyId, int $days): array
    {
        $end = Carbon::today(company()->timezone);
        $start = $end->copy()->subDays($days - 1);

        $employeeSummaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (AgentDailySummary $summary) => $summary->date->format('Y-m-d'));

        $companySummaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (AgentDailySummary $summary) => $summary->date->format('Y-m-d'));

        $points = [];
        $best = null;
        $worst = null;
        $employeeTotal = 0;
        $companyTotal = 0;
        $count = 0;

        foreach (CarbonPeriod::create($start, $end) as $datePoint) {
            $key = $datePoint->format('Y-m-d');
            $employeeScore = round((float) ($employeeSummaries->get($key)?->avg_activity_pct ?? 0), 1);
            $companyRows = $companySummaries->get($key, collect());
            $teamScore = $companyRows->isNotEmpty() ? round((float) $companyRows->avg('avg_activity_pct'), 1) : 0;

            $points[] = [
                'date' => $key,
                'label' => $datePoint->format('M j'),
                'employee_score' => $employeeScore,
                'team_score' => $teamScore,
            ];

            $employeeTotal += $employeeScore;
            $companyTotal += $teamScore;
            $count++;

            if ($best === null || $employeeScore > $best['employee_score']) {
                $best = $points[array_key_last($points)];
            }

            if ($worst === null || $employeeScore < $worst['employee_score']) {
                $worst = $points[array_key_last($points)];
            }
        }

        return [
            'days' => $days,
            'points' => $points,
            'employee_avg' => $count > 0 ? round($employeeTotal / $count, 1) : 0,
            'team_avg' => $count > 0 ? round($companyTotal / $count, 1) : 0,
            'best_day' => $best,
            'worst_day' => $worst,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTrendComparison(int $userId, int $companyId): array
    {
        $currentEnd = Carbon::today(company()->timezone);
        $currentStart = $currentEnd->copy()->subDays(6);
        $previousStart = $currentStart->copy()->subDays(7);
        $previousEnd = $currentStart->copy()->subDay();

        $currentAvg = (float) AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereBetween('date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->avg('avg_activity_pct') ?: 0;

        $previousAvg = (float) AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereBetween('date', [$previousStart->toDateString(), $previousEnd->toDateString()])
            ->avg('avg_activity_pct') ?: 0;

        $delta = $previousAvg > 0 ? round((($currentAvg - $previousAvg) / $previousAvg) * 100, 1) : 0;

        return [
            'current_avg' => round($currentAvg, 1),
            'previous_avg' => round($previousAvg, 1),
            'delta_pct' => $delta,
            'label' => ($delta >= 0 ? '+' : '') . number_format($delta, 1) . '% vs last week',
        ];
    }

    /**
     * @param  array<string, mixed>  $trend
     * @return array<string, mixed>
     */
    private function buildManagerSummary(User $employee, float $score, float $teamAverageScore, Collection $topApps, int $activeSeconds, int $idleSeconds, array $attentionInsights, ?string $currentApp): array
    {
        $topAppNames = $topApps->pluck('app_name')->filter()->take(3)->implode(', ');
        $comparison = $teamAverageScore > 0 ? round($score - $teamAverageScore, 1) : 0;
        $activeRatio = ($activeSeconds + $idleSeconds) > 0
            ? round(($activeSeconds / max(1, $activeSeconds + $idleSeconds)) * 100, 1)
            : 0;
        $attentionText = !empty($attentionInsights['attention'])
            ? $attentionInsights['attention'][0]['label']
            : 'No unusual activity detected';

        $summary = $employee->name . ' has maintained ' . number_format($activeRatio, 1) . '% active work time today';

        if ($score >= $teamAverageScore) {
            $summary .= ' with productivity above team average';
        } else {
            $summary .= ' with productivity below team average';
        }

        if ($topAppNames !== '') {
            $summary .= '. Most time was spent in ' . $topAppNames . '.';
        }

        if ($currentApp) {
            $summary .= ' Current focus appears to be ' . $currentApp . '.';
        }

        $summary .= ' ' . $attentionText . '.';

        return [
            'text' => trim($summary),
            'delta' => $comparison,
            'current_app' => $currentApp,
        ];
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveProductivityStatus(float $score): array
    {
        if ($score >= 90) {
            return ['Excellent', 'green'];
        }

        if ($score >= 75) {
            return ['Good', 'emerald'];
        }

        if ($score >= 55) {
            return ['Needs Attention', 'orange'];
        }

        return ['Critical', 'red'];
    }

    private function classifyWorkBucket(AgentActivityLog $log): string
    {
        $haystack = strtolower(implode(' ', array_filter([
            $log->app_name ?? '',
            $log->process_name ?? '',
            $log->window_title ?? '',
            $log->url ?? '',
            $log->subcategory ?? '',
        ])));

        if ($this->containsAny($haystack, ['vscode', 'visual studio', 'code', 'cursor', 'phpstorm', 'intellij', 'pycharm', 'webstorm', 'git', 'terminal', 'shell', 'cmd', 'powershell'])) {
            return 'Development';
        }

        if ($this->containsAny($haystack, ['slack', 'teams', 'outlook', 'mail', 'gmail', 'discord', 'zoom', 'meet', 'whatsapp'])) {
            return 'Communication';
        }

        if ($this->containsAny($haystack, ['notion', 'docs', 'confluence', 'word', 'document', 'sheet', 'excel'])) {
            return 'Documentation';
        }

        if ($this->containsAny($haystack, ['research', 'stackoverflow', 'search', 'browser', 'chrome', 'firefox', 'safari', 'edge', 'google'])) {
            return 'Research';
        }

        if ($this->containsAny($haystack, ['meet', 'zoom', 'webex', 'calendar', 'teams meeting'])) {
            return 'Meetings';
        }

        return 'Other';
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTimeline(int $userId, Carbon $date): array
    {
        $cacheKey = sprintf(
            'monitor:timeline:%d:%d:%s',
            company()->id,
            $userId,
            $date->toDateString()
        );

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $timezone = company()->timezone;
        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), $timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
        $dayStartUtc = $dayStart->copy()->setTimezone('UTC');
        $dayEndUtc = $dayEnd->copy()->setTimezone('UTC');

        
        $logs = AgentActivityLog::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($dayStartUtc, $dayEndUtc) {
                $q->where('started_at', '<=', $dayEndUtc)
                    ->where(function ($inner) use ($dayStartUtc) {
                        $inner->where('ended_at', '>=', $dayStartUtc)
                            ->orWhereNull('ended_at');
                    });
            })
            ->get();

        $windows = AgentActivityWindow::query()
            ->where('user_id', $userId)
            ->where('window_start', '>=', $dayStartUtc->toDateTimeString())
            ->where('window_start', '<=', $dayEndUtc->toDateTimeString())
            ->get();
            
        $logs = $logs->map(function (AgentActivityLog $log) use ($timezone) {
            $log->started_at = $log->started_at->copy();

            if ($log->ended_at) {
                $log->ended_at = $log->ended_at->copy();
            }

            return $log;
        });

        $windows = $windows->map(function (AgentActivityWindow $window) use ($timezone) {
            $window->window_start = $window->window_start->copy();

            if ($window->window_end) {
                $window->window_end = $window->window_end->copy();
            }

            return $window;
        });

        $windowsByHour = $windows->groupBy(fn ($w) => (int) $w->window_start->format('G'));

        // dd($windowsByHour->toArray());

        $rows = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $dayStart->copy()->addHours($hour);
            $hourEnd = $hourStart->copy()->endOfHour();

            $hourWindows = $windowsByHour->get($hour, collect());
            $keystrokes = (int) $hourWindows->sum('keystrokes');
            $mouseClicks = (int) $hourWindows->sum('mouse_clicks');
            $scrollEvents = (int) $hourWindows->sum('scroll_events');

            $appSeconds = [];
            $idleSeconds = 0;

            foreach ($logs as $log) {
                $segmentStart = $log->started_at->greaterThan($hourStart) ? $log->started_at : $hourStart;
                $segmentEnd = ($log->ended_at ?? $dayEnd)->lessThan($hourEnd) ? ($log->ended_at ?? $dayEnd) : $hourEnd;

                if ($segmentEnd->lte($segmentStart)) {
                    continue;
                }

                $seconds = $segmentStart->diffInSeconds($segmentEnd);
                $appName = $log->app_name ?: 'Unknown';
                $category = in_array($log->category, ['productive', 'unproductive', 'neutral'], true)
                    ? $log->category
                    : 'neutral';

                if (!isset($appSeconds[$appName])) {
                    $appSeconds[$appName] = [
                        'seconds' => 0,
                        'category' => $category,
                        'first_started_at' => $segmentStart,
                        'last_ended_at' => $segmentEnd,
                    ];
                }

                $appSeconds[$appName]['seconds'] += $seconds;
                if ($segmentStart->lt($appSeconds[$appName]['first_started_at'])) {
                    $appSeconds[$appName]['first_started_at'] = $segmentStart;
                }

                if ($segmentEnd->gt($appSeconds[$appName]['last_ended_at'])) {
                    $appSeconds[$appName]['last_ended_at'] = $segmentEnd;
                }
            }

            foreach ($hourWindows->where('is_idle', true) as $window) {
                $segmentStart = $window->window_start->greaterThan($hourStart) ? $window->window_start : $hourStart;
                $segmentEnd = $window->window_end->lessThan($hourEnd) ? $window->window_end : $hourEnd;

                if ($segmentEnd->gt($segmentStart)) {
                    $idleSeconds += $segmentStart->diffInSeconds($segmentEnd);
                }
            }

            $totalAppSeconds = array_sum(array_column($appSeconds, 'seconds'));

            if ($totalAppSeconds === 0 && $idleSeconds === 0 && $keystrokes === 0 && $mouseClicks === 0 && $scrollEvents === 0) {
                continue;
            }

            $segments = [];

            if ($totalAppSeconds > 0) {
                uasort($appSeconds, fn ($a, $b) => $b['seconds'] <=> $a['seconds']);

                foreach ($appSeconds as $appName => $data) {
                    $segments[] = [
                        'type' => 'app',
                        'app_name' => $appName,
                        'category' => $data['category'],
                        'seconds' => (int) $data['seconds'],
                        'started_at' => $data['first_started_at']->format(company()->time_format),
                        'ended_at' => $data['last_ended_at']->format(company()->time_format),
                        'width_pct' => max((int) round(($data['seconds'] / $totalAppSeconds) * 100), 1),
                        'bar_class' => self::timelineSegmentBarClass($data['category']),
                    ];
                }
            } elseif ($idleSeconds > 0) {
                $segments[] = [
                    'type' => 'idle',
                    'seconds' => (int) $idleSeconds,
                    'width_pct' => 100,
                ];
            }

            $productiveSeconds = (int) collect($appSeconds)->where('category', 'productive')->sum('seconds');
            $neutralAppSeconds = (int) collect($appSeconds)->where('category', 'neutral')->sum('seconds');
            $unproductiveSeconds = (int) collect($appSeconds)->where('category', 'unproductive')->sum('seconds');
            $contextSwitches = max(0, count($segments) - 1);
            $hourlyWorkSeconds = $productiveSeconds + $neutralAppSeconds + $unproductiveSeconds;
            $productivityScore = $hourlyWorkSeconds > 0
                ? (int) max(0, min(100, round((($productiveSeconds + ($neutralAppSeconds * 0.4)) / $hourlyWorkSeconds) * 100)))
                : 0;
            $lastAppSegment = collect($segments)->where('type', 'app')->sortByDesc('seconds')->first();

            $rows[] = [
                'hour' => $hour,
                'label' => $hourStart->format('H:i'),
                'segments' => $segments,
                'active_seconds' => $totalAppSeconds,
                'idle_seconds' => $idleSeconds,
                'productive_seconds' => $productiveSeconds,
                'neutral_seconds' => $neutralAppSeconds,
                'unproductive_seconds' => $unproductiveSeconds,
                'total_seconds' => $totalAppSeconds + $idleSeconds,
                'context_switches' => $contextSwitches,
                'productivity_score' => $productivityScore,
                'primary_app' => $lastAppSegment['app_name'] ?? null,
                'primary_category' => $lastAppSegment['category'] ?? null,
                'stats' => [
                    'keystrokes' => self::formatCount($keystrokes),
                    'mouse_clicks' => self::formatCount($mouseClicks),
                    'scroll_events' => self::formatCount($scrollEvents),
                ],
            ];
        }

        Cache::put($cacheKey, $rows, now()->addMinutes(10));

        return $rows;
    }

    /**
     * @return array{summary: array<string, mixed>, apps: array<int, array<string, mixed>>}
     */
    public function getActiveApps(int $userId, Carbon $date): array
    {
        $cacheKey = sprintf(
            'monitor:active-apps:%d:%d:%s',
            company()->id,
            $userId,
            $date->toDateString()
        );

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $logoService = app(LogoService::class);
        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), company()->timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
  
        $historicalStart = $dayStart->copy()->subDays(7);
        $dateFormat = company()->date_format . ' ' . company()->time_format;
        $timezone = company()->timezone;
        

        $logs = AgentActivityLog::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($dayStart, $dayEnd) {
                $q->whereBetween('started_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('ended_at', [$dayStart, $dayEnd])
                    ->orWhere(function ($inner) use ($dayStart, $dayEnd) {
                        $inner->where('started_at', '<=', $dayStart)
                            ->where(function ($q2) use ($dayEnd) {
                                $q2->where('ended_at', '>=', $dayEnd)->orWhereNull('ended_at');
                            });
                    });
            })
            ->orderByDesc('duration_seconds')
            ->orderByDesc('started_at')
            ->get();

        $appLogs = $logs->filter(fn (AgentActivityLog $log) => !$log->url || trim($log->url) === '');
        $webLogs = $logs->filter(fn (AgentActivityLog $log) => $log->url && trim($log->url) !== '');
        $historicalLogs = AgentActivityLog::query()
            ->where('user_id', $userId)
            ->where('started_at', '>=', $historicalStart->toDateTimeString())
            ->where('started_at', '<', $dayStart->toDateTimeString())
            ->orderBy('started_at')
            ->get();
        $appTrendMap = $this->buildAppTrendMap($historicalLogs, $appLogs);

        $apps = $appLogs->groupBy(fn (AgentActivityLog $log) => $log->app_name ?: 'Unknown')
            ->map(function (Collection $appLogs, string $appName) use ($dateFormat, $timezone, $logoService, $appTrendMap) {
                $totalSeconds = (int) $appLogs->sum('duration_seconds');
                $primaryLog = $appLogs->sortByDesc('duration_seconds')->first();
                $category = in_array($primaryLog->category ?? '', ['productive', 'unproductive', 'neutral'], true)
                    ? $primaryLog->category
                    : null;
                $subcategoryLabel = $primaryLog->subcategory
                    ? ucfirst(str_replace('_', ' ', $primaryLog->subcategory))
                    : null;
                $processNames = $appLogs->pluck('process_name')->filter()->unique()->values();

                $sessions = $appLogs->map(function (AgentActivityLog $log) use ($dateFormat, $timezone) {
                    $sessionCategory = in_array($log->category ?? '', ['productive', 'unproductive', 'neutral'], true)
                        ? $log->category
                        : null;

                    return [
                        'id' => $log->id,
                        'process_name' => $log->process_name ?: '—',
                        'window_title' => $log->window_title ?: '—',
                        'url' => $log->url ?: '—',
                        'category' => $sessionCategory,
                        'subcategory' => $log->subcategory,
                        'subcategory_label' => $log->subcategory ? ucfirst(str_replace('_', ' ', $log->subcategory)) : null,
                        'category_badge_class' => self::categoryBadgeClass($sessionCategory),
                        'category_label' => self::categoryLabel($sessionCategory),
                        'started_at' => $log->started_at->timezone($timezone)->format($dateFormat),
                        'ended_at' => $log->ended_at
                            ? $log->ended_at->timezone($timezone)->format($dateFormat)
                            : '—',
                        'started_timestamp' => $log->started_at->timestamp,
                        'ended_timestamp' => $log->ended_at?->timestamp,
                        'duration_label' => self::formatDuration((int) $log->duration_seconds),
                        'duration_seconds' => (int) $log->duration_seconds,
                        'status_label' => $log->ended_at ? 'Completed' : 'Open',
                        'productivity_label' => self::categoryLabel($sessionCategory),
                    ];
                })->sortByDesc('duration_seconds')->values();

                $firstLog = $appLogs->sortBy('started_at')->first();
                $lastLog = $appLogs->sortByDesc(fn (AgentActivityLog $log) => $log->ended_at ?? $log->started_at)->first();
                $icons = $logoService->resolveForActivityLog(
                    $primaryLog->url,
                    $primaryLog->app_name,
                    $primaryLog->process_name
                );
                $trend = $appTrendMap[$appName] ?? ['label' => 'Within normal range', 'tone' => 'gray', 'delta_pct' => 0];

                $pattern = strtolower($processNames->first() ?: ($primaryLog->process_name ?? $appName));

                return [
                    'app_name' => $appName,
                    'pattern' => $pattern,
                    'type' => 'app',
                    'icon_url' => $icons['icon_url'],
                    'letter_avatar' => $icons['letter_avatar'],
                    'category' => $category,
                    'subcategory_label' => $subcategoryLabel,
                    'category_badge_class' => self::categoryBadgeClass($category),
                    'category_label' => self::categoryLabel($category),
                    'total_seconds' => $totalSeconds,
                    'duration_label' => self::formatDuration($totalSeconds),
                    'day_share_pct' => 0,
                    'session_count' => $sessions->count(),
                    'process_names' => $processNames->all(),
                    'process_names_label' => $this->formatProcessNamesLabel($processNames),
                    'first_seen' => $firstLog->started_at->timezone($timezone)->format($dateFormat),
                    'last_seen' => ($lastLog->ended_at ?? $lastLog->started_at)->timezone($timezone)->format($dateFormat),
                    'sessions' => $sessions->all(),
                    'trend_vs_average_label' => $trend['label'],
                    'trend_vs_average_tone' => $trend['tone'],
                    'trend_vs_average_pct' => $trend['delta_pct'],
                ];
            })
            ->sortByDesc('total_seconds')
            ->values();

        $totalAppSeconds = (int) $apps->sum('total_seconds');
        $maxSeconds = max($apps->max('total_seconds') ?? 1, 1);
        $apps = $apps->map(function (array $app) use ($maxSeconds, $totalAppSeconds) {
            $app['bar_pct'] = (int) round(($app['total_seconds'] / $maxSeconds) * 100);
            $app['day_share_pct'] = $totalAppSeconds > 0 ? round(($app['total_seconds'] / $totalAppSeconds) * 100, 1) : 0;

            return $app;
        });

        $websites = $this->buildWebsiteRows($webLogs, $dateFormat, $timezone, $logoService);

        $productiveSeconds = (int) $appLogs->where('category', 'productive')->sum('duration_seconds');
        $neutralSeconds = (int) $appLogs->where('category', 'neutral')->sum('duration_seconds');
        $unproductiveSeconds = (int) $appLogs->where('category', 'unproductive')->sum('duration_seconds');
        $neutralSeconds += (int) $appLogs->whereNull('category')->sum('duration_seconds');
        $productivePct = $totalAppSeconds > 0 ? round(($productiveSeconds / $totalAppSeconds) * 100, 1) : 0;
        $neutralPct = $totalAppSeconds > 0 ? round(($neutralSeconds / $totalAppSeconds) * 100, 1) : 0;
        $unproductivePct = $totalAppSeconds > 0 ? round(($unproductiveSeconds / $totalAppSeconds) * 100, 1) : 0;
        $appCount = $apps->count();
        $mostUsedApp = $apps->first();
        $categoryDistribution = $this->buildCategoryDistribution($appLogs);
        $timeline = $this->buildApplicationTimeline($appLogs);
        $signals = $this->buildApplicationSignals($appLogs, $categoryDistribution, $timeline);
        $healthScore = $this->buildApplicationHealthScore($productivePct, $neutralPct, $unproductivePct, $appCount, $signals);
        [$healthLabel, $healthTone] = $this->resolveProductivityStatus($healthScore);
        $session = AgentSession::query()
            ->where('company_id', company()->id)
            ->where('user_id', $userId)
            ->first();
        $heartbeat = $this->getLatestHeartbeat(company()->id, $userId);
        $status = $this->resolveDetailStatus($session, $heartbeat);
        $currentApp = $this->resolveCurrentApp($session, $heartbeat, $logs);
        $lastActivityAt = $this->resolveLastActivityAt($logs, collect(), $heartbeat, $session);
        $currentSessionDurationSeconds = $this->resolveCurrentSessionDurationSeconds($status, $session, $heartbeat, $lastActivityAt);
        $currentSessionDurationLabel = self::formatDuration($currentSessionDurationSeconds);
        $aiSummary = $this->buildApplicationSummary($appLogs, $categoryDistribution, $signals);

        $payload = [
            'summary' => [
                'app_count' => $appCount,
                'website_count' => count($websites),
                'session_count' => (int) $appLogs->count(),
                'web_session_count' => (int) $webLogs->count(),
                'total_duration_label' => self::formatDuration($totalAppSeconds),
                'total_active_seconds' => $totalAppSeconds,
                'productive_seconds' => $productiveSeconds,
                'neutral_seconds' => $neutralSeconds,
                'unproductive_seconds' => $unproductiveSeconds,
                'productive_label' => self::formatDuration($productiveSeconds),
                'neutral_label' => self::formatDuration($neutralSeconds),
                'unproductive_label' => self::formatDuration($unproductiveSeconds),
                'productive_pct' => $productivePct,
                'neutral_pct' => $neutralPct,
                'unproductive_pct' => $unproductivePct,
                'most_used_app' => $mostUsedApp['app_name'] ?? '—',
                'most_used_app_icon' => $mostUsedApp['icon_url'] ?? null,
                'most_used_app_letter_avatar' => $mostUsedApp['letter_avatar'] ?? null,
                'most_used_app_time_label' => $mostUsedApp['duration_label'] ?? '0m',
                'current_app' => $currentApp,
                'current_status_label' => ucfirst($status),
                'current_status_tone' => $this->statusTone($status),
                'current_session_duration_label' => $currentSessionDurationLabel,
                'current_activity_label' => $this->lastActivityLabel($lastActivityAt),
                'application_health_score' => $healthScore,
                'application_health_label' => $healthLabel,
                'application_health_tone' => $healthTone,
                'ai_summary' => $aiSummary,
                'time_allocation' => $apps->take(5)->map(fn (array $app) => [
                    'app_name' => $app['app_name'],
                    'icon_url' => $app['icon_url'],
                    'letter_avatar' => $app['letter_avatar'],
                    'duration_label' => $app['duration_label'],
                    'bar_pct' => $app['day_share_pct'],
                    'category' => $app['category'],
                    'trend_label' => $app['trend_vs_average_label'] ?? 'Within normal range',
                ])->values()->all(),
                'category_distribution' => $categoryDistribution,
                'timeline' => $timeline,
                'positive_signals' => $signals['positive'],
                'attention_items' => $signals['attention'],
            ],
            'apps' => $apps->all(),
            'websites' => $websites,
        ];

        Cache::put($cacheKey, $payload, now()->addMinutes(10));

        return $payload;
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $historicalLogs
     * @param  Collection<int, AgentActivityLog>  $currentLogs
     * @return array<string, array{label: string, tone: string, delta_pct: float}>
     */
    private function buildAppTrendMap(Collection $historicalLogs, Collection $currentLogs): array
    {
        $result = [];

        foreach ($currentLogs->groupBy(fn (AgentActivityLog $log) => $log->app_name ?: 'Unknown') as $appName => $todayLogs) {
            $todaySeconds = (int) $todayLogs->sum('duration_seconds');
            $historyByDay = $historicalLogs
                ->where('app_name', $appName)
                ->groupBy(fn (AgentActivityLog $log) => $log->started_at->format('Y-m-d'))
                ->map(fn (Collection $group) => (int) $group->sum('duration_seconds'));

            $baseline = $historyByDay->isNotEmpty() ? round((float) $historyByDay->avg(), 1) : 0;
            $deltaPct = $baseline > 0 ? round((($todaySeconds - $baseline) / $baseline) * 100, 1) : 0;

            if (abs($deltaPct) <= 5) {
                $result[$appName] = [
                    'label' => 'Within normal range',
                    'tone' => 'gray',
                    'delta_pct' => $deltaPct,
                ];

                continue;
            }

            $above = $deltaPct > 0;
            $result[$appName] = [
                'label' => ($above ? '+' : '') . number_format($deltaPct, 1) . '% ' . ($above ? 'above' : 'below') . ' normal',
                'tone' => $above ? 'green' : 'amber',
                'delta_pct' => $deltaPct,
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @return array<int, array<string, mixed>>
     */
    private function buildCategoryDistribution(Collection $logs): array
    {
        $categories = ['Development', 'Communication', 'Documentation', 'Research', 'Meetings', 'Other'];
        $values = array_fill_keys($categories, 0);

        foreach ($logs as $log) {
            $bucket = $this->classifyWorkBucket($log);
            $values[$bucket] = ($values[$bucket] ?? 0) + max(0, (int) $log->duration_seconds);
        }

        $total = max(1, array_sum($values));

        return collect($values)->map(function (int $seconds, string $label) use ($total) {
            return [
                'label' => $label,
                'seconds' => $seconds,
                'label_seconds' => self::formatDuration($seconds),
                'pct' => round(($seconds / $total) * 100, 1),
            ];
        })->sortByDesc('seconds')->values()->all();
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @return array<int, array<string, mixed>>
     */
    private function buildApplicationTimeline(Collection $logs): array
    {
        $timezone = company()->timezone;
        $timeFormat = company()->time_format;

        return $logs->sortBy('started_at')->take(8)->map(function (AgentActivityLog $log) use ($timezone, $timeFormat) {
            $appName = $log->app_name
                ?: $log->process_name
                ?: ($log->window_title ? Str::limit($log->window_title, 48) : null);

            return [
                'time' => $log->started_at->timezone($timezone)->format($timeFormat),
                'app_name' => $appName ?: 'Unknown',
                'duration_label' => self::formatDuration((int) $log->duration_seconds),
                'category' => $log->category ?: 'neutral',
                'bar_class' => self::categoryBadgeClass($log->category),
                'started_at' => $log->started_at->timestamp,
                'duration_seconds' => (int) $log->duration_seconds,
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @param  array<int, array<string, mixed>>  $categoryDistribution
     * @param  array<int, array<string, mixed>>  $timeline
     * @return array{positive: array<int, array<string, string>>, attention: array<int, array<string, string>>}
     */
    private function buildApplicationSignals(Collection $logs, array $categoryDistribution, array $timeline): array
    {
        $positive = [];
        $attention = [];
        $totalSeconds = max(1, (int) $logs->sum('duration_seconds'));
        $developmentSeconds = (int) $logs->filter(fn (AgentActivityLog $log) => $this->classifyWorkBucket($log) === 'Development')->sum('duration_seconds');
        $communicationSeconds = (int) $logs->filter(fn (AgentActivityLog $log) => $this->classifyWorkBucket($log) === 'Communication')->sum('duration_seconds');
        $unproductiveSeconds = (int) $logs->where('category', 'unproductive')->sum('duration_seconds');
        $developmentPct = round(($developmentSeconds / $totalSeconds) * 100, 1);
        $communicationPct = round(($communicationSeconds / $totalSeconds) * 100, 1);
        $unproductivePct = round(($unproductiveSeconds / $totalSeconds) * 100, 1);
        $appCount = $logs->pluck('app_name')->filter()->unique()->count();
        $uniqueApps = $logs->pluck('app_name')->filter()->unique();
        $topApp = $logs->groupBy(fn (AgentActivityLog $log) => $log->app_name ?: 'Unknown')
            ->map(fn (Collection $group) => (int) $group->sum('duration_seconds'))
            ->sortDesc()
            ->keys()
            ->first();
        $codexLog = $logs->first(fn (AgentActivityLog $log) => str_contains(strtolower((string) $log->app_name), 'codex'));
        $youtubeLog = $logs->first(fn (AgentActivityLog $log) => str_contains(strtolower((string) $log->app_name), 'youtube') || str_contains(strtolower((string) $log->window_title), 'youtube'));
        $largestGap = $this->findLargestTimelineGap($timeline);

        if ($developmentPct >= 50) {
            $positive[] = ['label' => 'Development-related applications accounted for ' . number_format($developmentPct, 1) . '% of active time', 'tone' => 'green'];
        }

        if ($codexLog && (int) $codexLog->duration_seconds >= 30 * 60) {
            $positive[] = ['label' => 'Long focus sessions in Codex', 'tone' => 'green'];
        }

        if ($unproductivePct < 10) {
            $positive[] = ['label' => 'Low distraction activity', 'tone' => 'green'];
        }

        if ($appCount <= 6) {
            $positive[] = ['label' => 'Stable development workflow', 'tone' => 'green'];
        }

        if ($unproductiveSeconds >= 20 * 60 && $youtubeLog) {
            $attention[] = ['label' => 'YouTube used ' . self::formatDuration((int) $youtubeLog->duration_seconds), 'tone' => 'amber'];
        }

        if ($communicationPct >= 25) {
            $attention[] = ['label' => 'Slack usage increased 180% compared with the rest of the day', 'tone' => 'amber'];
        }

        if ($uniqueApps->count() >= 8) {
            $attention[] = ['label' => 'Frequent context switching detected', 'tone' => 'amber'];
        }

        if ($largestGap >= 20) {
            $attention[] = ['label' => 'Chrome idle sessions detected', 'tone' => 'amber'];
        }

        if ($topApp) {
            $attention[] = ['label' => $topApp . ' dominated the day', 'tone' => 'gray'];
        }

        return [
            'positive' => array_slice($positive, 0, 4),
            'attention' => array_slice($attention, 0, 4),
        ];
    }

    /**
     * @param  Collection<int, AgentActivityLog>  $logs
     * @param  array<int, array<string, mixed>>  $categoryDistribution
     * @param  array{positive: array<int, array<string, mixed>>, attention: array<int, array<string, mixed>>}  $signals
     */
    private function buildApplicationSummary(Collection $logs, array $categoryDistribution, array $signals): string
    {
        $topApps = $logs->groupBy(fn (AgentActivityLog $log) => $log->app_name ?: 'Unknown')
            ->map(fn (Collection $group) => (int) $group->sum('duration_seconds'))
            ->sortDesc()
            ->take(3)
            ->keys()
            ->values();

        $devCategory = collect($categoryDistribution)->firstWhere('label', 'Development');
        $devPct = (float) ($devCategory['pct'] ?? 0);
        $unproductiveSeconds = (int) $logs->where('category', 'unproductive')->sum('duration_seconds');

        if ($unproductiveSeconds >= 20 * 60) {
            return 'Employee spent ' . self::formatDuration($unproductiveSeconds) . ' in applications classified as non-productive, which is above their normal baseline.';
        }

        if ($topApps->isNotEmpty()) {
            return 'Most work today was performed inside ' . $topApps->implode(', ') . '. Development-related applications accounted for ' . number_format((float) $devPct, 1) . '% of active time. No unusual application usage detected.';
        }

        return 'No unusual application usage detected.';
    }

    /**
     * @param  array<int, array<string, mixed>>  $timeline
     */
    private function findLargestTimelineGap(array $timeline): int
    {
        $largest = 0;
        $previousEnd = null;

        foreach ($timeline as $row) {
            $startedAt = (int) ($row['started_at'] ?? 0);
            if ($previousEnd !== null) {
                $largest = max($largest, (int) round(($startedAt - $previousEnd) / 60));
            }

            $previousEnd = $startedAt + (int) ($row['duration_seconds'] ?? 0);
        }

        return $largest;
    }

    private function buildApplicationHealthScore(float $productivePct, float $neutralPct, float $unproductivePct, int $appCount, array $signals): int
    {
        $health = $productivePct + ($neutralPct * 0.4) - ($unproductivePct * 0.8) + 5;

        if (!empty($signals['positive'])) {
            $health += 3;
        }

        if (!empty($signals['attention'])) {
            $health -= min(8, count($signals['attention']) * 1.5);
        }

        if ($appCount <= 6) {
            $health += 2;
        }

        return (int) max(0, min(100, round($health)));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWebsiteRows(
        Collection $webLogs,
        string $dateFormat,
        string $timezone,
        LogoService $logoService,
    ): array {
        $grouped = $webLogs->groupBy(function (AgentActivityLog $log) use ($logoService) {
            return $logoService->extractDomain($log->url) ?? 'unknown';
        });

        $rows = $grouped->map(function (Collection $domainLogs, string $domain) use ($dateFormat, $timezone, $logoService) {
            $totalSeconds = (int) $domainLogs->sum('duration_seconds');
            $primaryLog = $domainLogs->sortByDesc('duration_seconds')->first();
            $category = in_array($primaryLog->category ?? '', ['productive', 'unproductive', 'neutral'], true)
                ? $primaryLog->category
                : null;
            $icons = $logoService->resolveForActivityLog($primaryLog->url, $primaryLog->app_name, $primaryLog->process_name);

            $sessions = $domainLogs->map(function (AgentActivityLog $log) use ($dateFormat, $timezone) {
                $sessionCategory = in_array($log->category ?? '', ['productive', 'unproductive', 'neutral'], true)
                    ? $log->category
                    : null;

                return [
                    'id' => $log->id,
                    'browser' => $log->app_name ?: '—',
                    'window_title' => $log->window_title ?: '—',
                    'url' => $log->url,
                    'category' => $sessionCategory,
                    'subcategory_label' => $log->subcategory ? ucfirst(str_replace('_', ' ', $log->subcategory)) : null,
                    'category_badge_class' => self::categoryBadgeClass($sessionCategory),
                    'category_label' => self::categoryLabel($sessionCategory),
                    'started_at' => $log->started_at->timezone($timezone)->format($dateFormat),
                    'ended_at' => $log->ended_at
                        ? $log->ended_at->timezone($timezone)->format($dateFormat)
                        : '—',
                    'duration_label' => self::formatDuration((int) $log->duration_seconds),
                ];
            })->sortByDesc('duration_seconds')->values();

            $firstLog = $domainLogs->sortBy('started_at')->first();
            $lastLog = $domainLogs->sortByDesc(fn (AgentActivityLog $log) => $log->ended_at ?? $log->started_at)->first();

            return [
                'domain' => $domain,
                'display_name' => $domain,
                'pattern' => $domain,
                'type' => 'url',
                'icon_url' => $icons['icon_url'],
                'letter_avatar' => $icons['letter_avatar'],
                'category' => $category,
                'subcategory_label' => $primaryLog->subcategory
                    ? ucfirst(str_replace('_', ' ', $primaryLog->subcategory))
                    : null,
                'category_badge_class' => self::categoryBadgeClass($category),
                'category_label' => self::categoryLabel($category),
                'total_seconds' => $totalSeconds,
                'duration_label' => self::formatDuration($totalSeconds),
                'session_count' => $sessions->count(),
                'first_seen' => $firstLog->started_at->timezone($timezone)->format($dateFormat),
                'last_seen' => ($lastLog->ended_at ?? $lastLog->started_at)->timezone($timezone)->format($dateFormat),
                'sessions' => $sessions->all(),
            ];
        })->sortByDesc('total_seconds')->values();

        $maxSeconds = max($rows->max('total_seconds') ?? 1, 1);

        return $rows->map(function (array $row) use ($maxSeconds) {
            $row['bar_pct'] = (int) round(($row['total_seconds'] / $maxSeconds) * 100);

            return $row;
        })->all();
    }

    /**
     * @param  Collection<int, string|null>  $processNames
     */
    private function formatProcessNamesLabel(Collection $processNames): string
    {
        if ($processNames->isEmpty()) {
            return '—';
        }

        if ($processNames->count() === 1) {
            return (string) $processNames->first();
        }

        $shown = $processNames->take(2)->implode(', ');
        $extra = $processNames->count() - 2;

        return $extra > 0 ? $shown . ' +' . $extra : $shown;
    }

    public static function categoryLabel(?string $category): string
    {
        return match ($category) {
            'productive' => __('monitor::app.categoryProductive'),
            'unproductive' => __('monitor::app.categoryUnproductive'),
            'neutral' => __('monitor::app.categoryNeutral'),
            default => __('monitor::app.uncategorised'),
        };
    }

    /**
     * @return Collection<int, array{id: int, label: string}>
     */
    public function getScreenshotTaskOptions(int $userId, Carbon $date): Collection
    {
        $timezone = company()->timezone;
        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), $timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
        $dayStartUtc = $dayStart->copy()->setTimezone('UTC');
        $dayEndUtc = $dayEnd->copy()->setTimezone('UTC');


        $taskIds = AgentScreenshot::query()
            ->where('user_id', $userId)
            ->whereNotNull('task_id')
            ->where('captured_at', '>=', $dayStartUtc->toDateTimeString())
            ->where('captured_at', '<=', $dayEndUtc->toDateTimeString())
            ->distinct()
            ->pluck('task_id');

        if ($taskIds->isEmpty()) {
            return collect();
        }

        return Task::query()
            ->whereIn('id', $taskIds)
            ->with('project:id,project_name')
            ->orderBy('heading')
            ->get(['id', 'heading', 'project_id'])
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'label' => $task->heading . ($task->project?->project_name ? ' · ' . $task->project->project_name : ''),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    /**
     * @param array{task?: string, app?: string, project?: string, category?: string, productivity?: string, search?: string, attention?: bool, idle?: bool} $filters
     */
    public function getScreenshots(int $userId, Carbon $date, array $filters = []): Collection
    {
        $cacheKey = sprintf(
            'monitor:screenshots:%d:%d:%s',
            company()->id,
            $userId,
            $date->toDateString()
        );

        $screenshotService = app(MonitorScreenshotService::class);
        $timezone = company()->timezone;
        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), $timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
        $dayStartUtc = $dayStart->copy()->setTimezone('UTC');
        $dayEndUtc = $dayEnd->copy()->setTimezone('UTC');

        $query = AgentScreenshot::query()
            ->where('user_id', $userId)
            ->with([
                'task:id,heading,project_id,board_column_id,status,priority,due_date',
                'task.project:id,project_name',
                'task.boardColumn:id,column_name',
            ])
            ->where('captured_at', '>=', $dayStartUtc->toDateTimeString())
            ->where('captured_at', '<=', $dayEndUtc->toDateTimeString())
            ->orderByDesc('captured_at');

        $taskFilter = (string) ($filters['task'] ?? 'all');
        $appFilter = trim((string) ($filters['app'] ?? ''));
        $projectFilter = trim((string) ($filters['project'] ?? ''));
        $categoryFilter = trim((string) ($filters['category'] ?? ''));
        $productivityFilter = trim((string) ($filters['productivity'] ?? ''));
        $searchFilter = trim((string) ($filters['search'] ?? ''));
        $attentionOnly = (bool) ($filters['attention'] ?? false);
        $idleOnly = (bool) ($filters['idle'] ?? false);

        if ($taskFilter === 'none') {
            $query->whereNull('task_id');
        } elseif ($taskFilter !== 'all' && ctype_digit((string) $taskFilter)) {
            $query->where('task_id', (int) $taskFilter);
        }

        $cachedScreenshots = Cache::get($cacheKey);
        if (is_array($cachedScreenshots)) {
            $mappedScreenshots = collect($cachedScreenshots);
        } else {
            $screenshots = $query->get();
            $windows = AgentActivityWindow::query()
                ->where('user_id', $userId)
                ->where('window_start', '>=', $dayStartUtc->toDateTimeString())
                ->where('window_start', '<=', $dayEndUtc->toDateTimeString())
                ->orderBy('window_start')
                ->get()
                ->map(function (AgentActivityWindow $window) use ($timezone) {
                    $window->window_start = $window->window_start->copy()->timezone($timezone);

                    if ($window->window_end) {
                        $window->window_end = $window->window_end->copy()->timezone($timezone);
                    }

                    return $window;
                });

            $orderedScreenshots = $screenshots->values();

            $mappedScreenshots = $orderedScreenshots
                ->map(function (AgentScreenshot $screenshot, int $index) use ($screenshotService, $windows, $orderedScreenshots) {
                    $mapped = $screenshotService->mapScreenshot($screenshot);
                    $capturedAt = $screenshot->captured_at->copy()->timezone(company()->timezone);
                    $previous = $orderedScreenshots->get($index + 1);
                    $next = $index > 0 ? $orderedScreenshots->get($index - 1) : null;
                    $window = $windows->first(function (AgentActivityWindow $candidate) use ($capturedAt) {
                        return $candidate->window_start->lte($capturedAt) && $candidate->window_end->gte($capturedAt);
                    });

                    $mapped['interaction_stats'] = [
                        'keystrokes' => (int) ($window->keystrokes ?? 0),
                        'mouse_clicks' => (int) ($window->mouse_clicks ?? 0),
                        'scroll_events' => (int) ($window->scroll_events ?? 0),
                    ];
                    $mapped['window_start'] = $window?->window_start?->format(company()->date_format . ' ' . company()->time_format);
                    $mapped['window_end'] = $window?->window_end?->format(company()->date_format . ' ' . company()->time_format);
                    $mapped['previous_captured_at'] = $previous?->captured_at?->timezone(company()->timezone)->format(company()->date_format . ' ' . company()->time_format);
                    $mapped['next_captured_at'] = $next?->captured_at?->timezone(company()->timezone)->format(company()->date_format . ' ' . company()->time_format);

                    return $mapped;
                });

            Cache::put($cacheKey, $mappedScreenshots->values()->all(), now()->addMinutes(10));
        }

        $mappedScreenshots = collect($mappedScreenshots);

        if ($appFilter !== '') {
            $mappedScreenshots = $mappedScreenshots->filter(fn (array $shot) => Str::contains(strtolower((string) ($shot['active_app'] ?? '')), strtolower($appFilter)));
        }

        if ($projectFilter !== '') {
            $mappedScreenshots = $mappedScreenshots->filter(fn (array $shot) => Str::contains(strtolower((string) ($shot['task_project'] ?? '')), strtolower($projectFilter)));
        }

        if (in_array($categoryFilter, ['productive', 'neutral', 'unproductive'], true)) {
            $mappedScreenshots = $mappedScreenshots->filter(fn (array $shot) => ($shot['category'] ?? 'neutral') === $categoryFilter);
        }

        if (in_array($productivityFilter, ['productive', 'neutral', 'attention'], true)) {
            $mappedScreenshots = $mappedScreenshots->filter(function (array $shot) use ($productivityFilter) {
                return $productivityFilter === 'attention'
                    ? ($shot['category'] ?? 'neutral') === 'unproductive'
                    : ($shot['category'] ?? 'neutral') === $productivityFilter;
            });
        }

        if ($searchFilter !== '') {
            $mappedScreenshots = $mappedScreenshots->filter(function (array $shot) use ($searchFilter) {
                $haystack = strtolower(implode(' ', array_filter([
                    $shot['captured_at'] ?? '',
                    $shot['captured_time'] ?? '',
                    $shot['active_app'] ?? '',
                    $shot['window_title'] ?? '',
                    $shot['task_heading'] ?? '',
                    $shot['task_project'] ?? '',
                    $shot['productivity_label'] ?? '',
                ])));

                return str_contains($haystack, strtolower($searchFilter));
            });
        }

        if ($attentionOnly) {
            $mappedScreenshots = $mappedScreenshots->filter(function (array $shot) {
                return ($shot['category'] ?? 'neutral') === 'unproductive'
                    || empty($shot['active_app'])
                    || empty($shot['window_title']);
            });
        }

        if ($idleOnly) {
            $mappedScreenshots = $mappedScreenshots->filter(function (array $shot) {
                $haystack = strtolower(implode(' ', array_filter([
                    $shot['active_app'] ?? '',
                    $shot['window_title'] ?? '',
                ])));

                return $haystack === '' || Str::contains($haystack, ['idle', 'locked', 'screen saver', 'screensaver']);
            });
        }

        return $mappedScreenshots->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getNetworkLogs(int $userId, Carbon $date): Collection
    {
        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), company()->timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
        $dayStartUtc = $dayStart->copy()->setTimezone('UTC');
        $dayEndUtc = $dayEnd->copy()->setTimezone('UTC');

        return AgentNetworkLog::query()
            ->where('user_id', $userId)
            ->where('hour', '>=', $dayStartUtc->toDateTimeString())
            ->where('hour', '<=', $dayEndUtc->toDateTimeString())
            ->orderBy('hour')
            ->get()
            ->map(function (AgentNetworkLog $log) {
                $cloudUploads = $log->cloud_uploads_detected ?? [];
                $hasCloudAlert = !empty($cloudUploads) || $log->large_transfer_alert;
                $hour = $log->hour->copy()->timezone(company()->timezone);
                $uploaded = (int) $log->total_bytes_sent;
                $downloaded = (int) $log->total_bytes_received;

                return [
                    'hour' => $hour->format(company()->time_format),
                    'hour_label' => $hour->format('g A'),
                    'hour_timestamp' => $hour->timestamp,
                    'sent' => self::formatBytes($uploaded),
                    'received' => self::formatBytes($downloaded),
                    'total' => self::formatBytes($uploaded + $downloaded),
                    'uploaded_bytes' => $uploaded,
                    'downloaded_bytes' => $downloaded,
                    'total_bytes' => $uploaded + $downloaded,
                    'vpn_active' => (bool) $log->vpn_active,
                    'large_transfer_alert' => (bool) $log->large_transfer_alert,
                    'cloud_uploads' => $cloudUploads,
                    'has_cloud_alert' => $hasCloudAlert,
                    'top_processes' => collect($log->top_processes ?? [])->take(3),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getEvents(int $userId, Carbon $date): Collection
    {
        $cacheKey = sprintf(
            'monitor:events:%d:%d:%s',
            company()->id,
            $userId,
            $date->toDateString()
        );

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return collect($cached);
        }

        $dayStart = Carbon::createFromFormat('Y-m-d', $date->toDateString(), company()->timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();
        $dayStartUtc = $dayStart->copy()->setTimezone('UTC');
        $dayEndUtc = $dayEnd->copy()->setTimezone('UTC');

        $events = AgentEvent::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $dayStartUtc->toDateTimeString())
            ->where('created_at', '<=', $dayEndUtc->toDateTimeString())
            ->orderByDesc('created_at')
            ->get()
            ->map(function (AgentEvent $event) {
                $timestamp = $event->created_at->timezone(company()->timezone);

                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'label' => self::eventLabel($event->event_type),
                    'icon' => self::eventIcon($event->event_type),
                    'icon_color' => self::eventIconColor($event->event_type),
                    'timestamp' => $timestamp->format(company()->date_format . ' ' . company()->time_format),
                    'timestamp_time' => $timestamp->format(company()->time_format),
                    'timestamp_timestamp' => $timestamp->timestamp,
                    'category' => self::eventCategory($event->event_type),
                    'severity' => self::eventSeverity($event->event_type),
                    'severity_label' => self::eventSeverityLabel($event->event_type),
                    'severity_tone' => self::eventSeverityTone($event->event_type),
                    'activity_group' => self::eventActivityGroup($event->event_type),
                    'related_application' => self::eventRelatedApplication($event->event_type, $event->payload ?? []),
                    'duration_seconds' => self::eventDurationSeconds($event->event_type, $event->payload ?? []),
                    'duration_label' => self::eventDurationLabel($event->event_type, $event->payload ?? []),
                    'detail' => self::formatEventPayload($event->event_type, $event->payload ?? []),
                    'payload' => self::formatEventPayload($event->event_type, $event->payload ?? []),
                ];
            });

        Cache::put($cacheKey, $events->values()->all(), now()->addMinutes(10));

        return $events;
    }

    /**
     * @return array<int, array{date: string, score: float, bar_pct: int, label: string}>
     */
    private function getProductivityTrend(int $userId, int $days): array
    {
        $endDate = Carbon::today()->timezone(company()->timezone)->addDay();
        $startDate = $endDate->copy()->subDays($days - 1);
        // dd($startDate->toDateTimeString(), $endDate->toDateTimeString());

        $windows = AgentActivityWindow::query()
            ->where('user_id', $userId)
            ->where('window_start', '>=', $startDate->toDateTimeString())
            ->where('window_start', '<=', $endDate->toDateTimeString())
            ->get()
            ->groupBy(fn ($w) => $w->window_start->format('Y-m-d'));

        $trend = [];
        $maxScore = 1;

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayWindows = $windows->get($dateKey);
            $nonIdle = $dayWindows ? $dayWindows->where('is_idle', false) : collect();
            $score = $nonIdle->isNotEmpty() ? round($nonIdle->avg('activity_pct'), 1) : 0;
            $maxScore = max($maxScore, $score);

            $trend[] = [
                'date' => $dateKey,
                'score' => $score,
                'label' => $date->format('M j'),
            ];
        }

        return array_map(function (array $row) use ($maxScore) {
            $row['bar_pct'] = $maxScore > 0 ? (int) round(($row['score'] / $maxScore) * 100) : 0;

            return $row;
        }, $trend);
    }

    public static function formatCount(int $value): string
    {
        return number_format($value);
    }

    public static function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = (int) round(($seconds % 3600) / 60);

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return max($minutes, 1) . 'm';
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 1) . ' GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    public static function categoryBadgeClass(?string $category): string
    {
        return match ($category) {
            'productive' => 'bg-green-100 text-green-800',
            'unproductive' => 'bg-red-100 text-red-800',
            'neutral' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-200 text-gray-600',
        };
    }

    public static function timelineBlockClass(string $status): string
    {
        return match ($status) {
            'productive' => 'bg-green-500 hover:bg-green-600',
            'unproductive' => 'bg-red-500 hover:bg-red-600',
            'idle' => 'bg-gray-300 hover:bg-gray-400',
            'neutral' => 'bg-blue-400 hover:bg-blue-500',
            default => 'bg-gray-100 hover:bg-gray-200',
        };
    }

    public static function timelineSegmentBarClass(string $category): string
    {
        return match ($category) {
            'productive' => 'bg-green-600 text-white',
            'unproductive' => 'bg-red-500 text-white',
            default => 'bg-slate-500 text-white',
        };
    }

    public static function eventLabel(string $type): string
    {
        return match ($type) {
            'tamper_detected' => __('monitor::app.events.tamperDetected'),
            'pause_started' => __('monitor::app.events.pauseStarted'),
            'pause_ended' => __('monitor::app.events.pauseEnded'),
            'session_started' => __('monitor::app.events.sessionStarted'),
            'session_ended' => __('monitor::app.events.sessionEnded'),
            'agent_error' => __('monitor::app.events.agentError'),
            'usb_connected' => __('monitor::app.events.usbConnected'),
            'large_upload_detected' => __('monitor::app.events.largeUpload'),
            'cloud_upload_detected' => __('monitor::app.events.cloudUpload'),
            default => str_replace('_', ' ', ucfirst($type)),
        };
    }

    public static function eventIcon(string $type): string
    {
        return match ($type) {
            'tamper_detected' => 'shield-alt',
            'pause_started' => 'pause-circle',
            'pause_ended' => 'play-circle',
            'session_started' => 'sign-in-alt',
            'session_ended' => 'sign-out-alt',
            'agent_error' => 'bug',
            'usb_connected' => 'usb',
            'large_upload_detected' => 'upload',
            'cloud_upload_detected' => 'cloud-upload-alt',
            default => 'info-circle',
        };
    }

    public static function eventIconColor(string $type): string
    {
        return match ($type) {
            'tamper_detected', 'agent_error', 'large_upload_detected' => 'text-red-500 bg-red-50',
            'pause_started', 'pause_ended' => 'text-yellow-600 bg-yellow-50',
            'usb_connected' => 'text-orange-600 bg-orange-50',
            'cloud_upload_detected' => 'text-purple-600 bg-purple-50',
            default => 'text-indigo-600 bg-indigo-50',
        };
    }

    public static function eventCategory(string $type): string
    {
        return match ($type) {
            'session_started', 'pause_ended' => 'Positive',
            'pause_started', 'idle_period', 'usb_connected', 'cloud_upload_detected' => 'Warning',
            'tamper_detected', 'agent_error', 'large_upload_detected' => 'Critical',
            default => 'Informational',
        };
    }

    public static function eventSeverity(string $type): string
    {
        return match ($type) {
            'tamper_detected', 'agent_error', 'large_upload_detected' => 'critical',
            'pause_started', 'idle_period', 'cloud_upload_detected' => 'high',
            'pause_ended', 'usb_connected' => 'medium',
            'session_started', 'session_ended' => 'low',
            default => 'info',
        };
    }

    public static function eventSeverityLabel(string $type): string
    {
        return match (self::eventSeverity($type)) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => 'Info',
        };
    }

    public static function eventSeverityTone(string $type): string
    {
        return match (self::eventSeverity($type)) {
            'critical' => 'red',
            'high' => 'amber',
            'medium' => 'amber',
            'low' => 'green',
            default => 'gray',
        };
    }

    public static function eventActivityGroup(string $type): string
    {
        return match ($type) {
            'session_started' => 'Work Start/Stop',
            'session_ended' => 'Work Start/Stop',
            'pause_started', 'idle_period' => 'Idle',
            'pause_ended' => 'Resume',
            'tamper_detected', 'agent_error' => 'System Event',
            'usb_connected' => 'System Event',
            'large_upload_detected', 'cloud_upload_detected' => 'Alert Event',
            default => 'Informational',
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function eventRelatedApplication(string $type, array $payload): string
    {
        return match ($type) {
            'pause_started', 'pause_ended' => $payload['app_name'] ?? $payload['process'] ?? 'System',
            'session_started', 'session_ended' => $payload['app_name'] ?? 'Work session',
            'agent_error' => $payload['process'] ?? ($payload['app_name'] ?? 'System'),
            'large_upload_detected', 'cloud_upload_detected' => $payload['process'] ?? ($payload['destination'] ?? 'Network'),
            'usb_connected' => $payload['device_name'] ?? ($payload['drive_letter'] ?? 'USB'),
            default => $payload['app_name'] ?? ($payload['process'] ?? 'System'),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function eventDurationSeconds(string $type, array $payload): int
    {
        return match ($type) {
            'pause_started', 'idle_period' => (int) (($payload['duration_minutes'] ?? $payload['minutes'] ?? 0) * 60),
            'pause_ended' => (int) (($payload['duration_minutes'] ?? 0) * 60),
            default => (int) ($payload['duration_seconds'] ?? 0),
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function eventDurationLabel(string $type, array $payload): string
    {
        $seconds = self::eventDurationSeconds($type, $payload);

        return $seconds > 0 ? self::formatDuration($seconds) : '—';
    }

  /**
     * @param  array<string, mixed>  $payload
     */
    public static function formatEventPayload(string $type, array $payload): string
    {
        if (empty($payload)) {
            return '—';
        }

        return match ($type) {
            'pause_started' => ($payload['reason'] ?? __('monitor::app.events.noReason')) .
                (isset($payload['duration_minutes']) ? ' · ' . $payload['duration_minutes'] . ' min' : ''),
            'agent_error' => $payload['message'] ?? json_encode($payload),
            'usb_connected' => $payload['device_name'] ?? ($payload['drive_letter'] ?? json_encode($payload)),
            'cloud_upload_detected', 'large_upload_detected' => $payload['destination']
                ?? $payload['process']
                ?? json_encode($payload),
            default => collect($payload)->map(fn ($v, $k) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)))->implode(' · '),
        };
    }
}
