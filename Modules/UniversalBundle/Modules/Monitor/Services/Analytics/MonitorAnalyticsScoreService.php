<?php

namespace Modules\Monitor\Services\Analytics;

use App\Models\Team;
use App\Models\User;
use Modules\Monitor\Services\Analytics\PeriodHelper;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Modules\Monitor\Entities\AgentDailySummary;
use Modules\Monitor\Entities\AgentSession;
use Modules\Monitor\Services\MonitorPermissionScope;
use Modules\Monitor\Support\ListSearch;

class MonitorAnalyticsScoreService
{
    public function __construct(
        private readonly MonitorPermissionScope $permissionScope,
    ) {
    }
    /**
     * @return array<string, mixed>
     */
    public function getScoresList(int $companyId, string $period, ?int $departmentId = null, bool $belowSixtyOnly = false, string $search = ''): array
    {
        [$start, $end] = PeriodHelper::resolve($period);
        $employees = $this->getEmployees($companyId, $departmentId);
        $userIds = $employees->pluck('id');

        $summaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');

        $sessions = AgentSession::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $rows = [];

        foreach ($employees as $employee) {
            $userSummaries = $summaries->get($employee->id, collect());

            if ($userSummaries->isEmpty()) {
                continue;
            }

            $avgScore = round($userSummaries->avg('avg_activity_pct'), 1);
            $activeSeconds = (int) $userSummaries->sum('active_seconds');

            if ($belowSixtyOnly && $avgScore >= 60) {
                continue;
            }

            $rows[] = [
                'user_id' => $employee->id,
                'name' => $employee->name,
                'image_url' => $employee->image_url,
                'department' => $employee->employeeDetail?->department?->team_name ?? '—',
                'department_id' => $employee->employeeDetail?->department_id,
                'score' => $avgScore,
                'active_seconds' => $activeSeconds,
                'active_hours_label' => MonitorAnalyticsHelper::formatDuration($activeSeconds),
                'active_hours_decimal' => MonitorAnalyticsHelper::decimalHours($activeSeconds),
                'is_online' => (bool) ($sessions->get($employee->id)?->is_online),
                'detail_url' => route('monitor.analytics.scores.show', $employee->id) . '?period=' . $period,
                'employee_url' => route('monitor.show', $employee->id),
            ];
        }

        $search = ListSearch::normalize($search);
        $rows = ListSearch::filterRows($rows, $search, ['name', 'department']);

        usort($rows, fn ($a, $b) => $b['score'] <=> $a['score']);

        $ranked = [];
        foreach ($rows as $i => $row) {
            $row['rank'] = $i + 1;
            $ranked[] = $row;
        }

        $teamAvgScore = count($ranked) > 0 ? round(collect($ranked)->avg('score'), 1) : 0;
        $teamAvgHours = count($ranked) > 0
            ? MonitorAnalyticsHelper::formatDuration((int) round(collect($ranked)->avg('active_seconds')))
            : '0m';

        $onlineCount = AgentSession::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->where('is_online', true)
            ->count();

        return [
            'period' => $period,
            'rows' => $ranked,
            'team_avg_score' => $teamAvgScore,
            'team_avg_hours' => $teamAvgHours,
            'total_employees' => $employees->count(),
            'shown_count' => count($ranked),
            'online_count' => $onlineCount,
            'departments' => Team::where('company_id', $companyId)->orderBy('team_name')->get(['id', 'team_name']),
            'department_id' => $departmentId,
            'below_sixty_only' => $belowSixtyOnly,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getEmployeeScoreDetail(int $companyId, int $userId): array
    {
        $employee = User::with('employeeDetail.department')
            ->where('company_id', $companyId)
            ->findOrFail($userId);

        $tz = company()->timezone;
        $today = Carbon::today($tz)->toDateString();
        $summaries = AgentDailySummary::query()
            ->where('user_id', $userId)
            ->where('date', '>=', Carbon::today($tz)->subDays(29)->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($s) => $s->date->format('Y-m-d'));

        $chart = [];
        $maxScore = 1;

        foreach (CarbonPeriod::create(Carbon::today($tz)->subDays(29), Carbon::today($tz)) as $date) {
            $key = $date->format('Y-m-d');
            $summary = $summaries->get($key);
            $score = $summary ? round($summary->avg_activity_pct, 1) : 0;
            $maxScore = max($maxScore, $score);

            $chart[] = [
                'date' => $key,
                'label' => $date->format('M j'),
                'score' => $score,
                'bar_pct' => 0,
            ];
        }

        $chart = array_map(function (array $day) use ($maxScore) {
            $day['bar_pct'] = $maxScore > 0 ? (int) round(($day['score'] / $maxScore) * 100) : 0;

            return $day;
        }, $chart);

        $todayScore = round((float) ($summaries->get($today)?->avg_activity_pct ?? 0), 1);

        [$weekStart] = PeriodHelper::resolve(PeriodHelper::THIS_WEEK, $tz);
        [$lastWeekStart, $lastWeekEnd] = PeriodHelper::previous(PeriodHelper::THIS_WEEK, $tz);

        $thisWeek = $summaries->filter(fn ($s) => $s->date->gte($weekStart));
        $lastWeek = $summaries->filter(fn ($s) => $s->date->between($lastWeekStart, $lastWeekEnd));

        $weekAvg = $thisWeek->isNotEmpty() ? round($thisWeek->avg('avg_activity_pct'), 1) : 0;
        $lastWeekAvg = $lastWeek->isNotEmpty() ? round($lastWeek->avg('avg_activity_pct'), 1) : 0;
        $personalBest = $summaries->isNotEmpty() ? round($summaries->max('avg_activity_pct'), 1) : 0;

        return [
            'employee' => $employee,
            'today_score' => $todayScore,
            'week_avg' => $weekAvg,
            'last_week_avg' => $lastWeekAvg,
            'personal_best' => $personalBest,
            'motivation' => MonitorAnalyticsHelper::motivationalLabel($todayScore),
            'chart' => $chart,
            'chart_start' => $chart[0]['label'] ?? '',
            'chart_end' => $chart[count($chart) - 1]['label'] ?? '',
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function getEmployees(int $companyId, ?int $departmentId = null): Collection
    {
        return $this->permissionScope
            ->getEmployees($companyId, $departmentId)
            ->load(['employeeDetail.department']);
    }
}
