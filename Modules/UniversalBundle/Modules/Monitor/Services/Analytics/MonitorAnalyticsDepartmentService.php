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
use Modules\Monitor\Support\ListSearch;

class MonitorAnalyticsDepartmentService
{
    public function __construct(
        private readonly MonitorAnalyticsScoreService $scoreService,
        private readonly ActivityUsageService $activityUsageService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOverview(int $companyId, string $period, string $search = ''): array
    {
        [$start, $end] = PeriodHelper::resolve($period);
        [$prevStart, $prevEnd] = PeriodHelper::previous($period);

        $departments = Team::where('company_id', $companyId)->orderBy('team_name')->get();
        $employees = $this->scoreService->getEmployees($companyId);
        $userIds = $employees->pluck('id');

        $summaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $prevSummaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->get();

        $byDept = $employees->groupBy(fn ($e) => $e->employeeDetail?->department_id ?? 0);
        $rows = [];

        foreach ($departments as $dept) {
            $deptUsers = $byDept->get($dept->id, collect())->pluck('id');
            $deptSummaries = $summaries->whereIn('user_id', $deptUsers);
            $prevDeptSummaries = $prevSummaries->whereIn('user_id', $deptUsers);

            if ($deptSummaries->isEmpty()) {
                continue;
            }

            $avgScore = round($deptSummaries->avg('avg_activity_pct'), 1);
            $prevScore = $prevDeptSummaries->isNotEmpty() ? round($prevDeptSummaries->avg('avg_activity_pct'), 1) : 0;
            $hours = MonitorAnalyticsHelper::formatDuration((int) $deptSummaries->sum('active_seconds'));

            $rows[] = [
                'id' => $dept->id,
                'name' => $dept->team_name,
                'score' => $avgScore,
                'headcount' => $deptUsers->count(),
                'hours' => $hours,
                'trend' => MonitorAnalyticsHelper::trend($avgScore, $prevScore),
                'url' => route('monitor.analytics.departments.show', $dept->id) . '?period=' . urlencode($period),
            ];
        }

        usort($rows, fn ($a, $b) => $b['score'] <=> $a['score']);
        $rows = ListSearch::filterRows($rows, ListSearch::normalize($search), ['name']);

        $companyAvg = $summaries->isNotEmpty() ? round($summaries->avg('avg_activity_pct'), 1) : 0;
        $onlineCount = AgentSession::where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->where('is_online', true)
            ->count();
        $atRisk = $this->countAtRisk($companyId, $userIds->all());
        $topDept = $rows[0]['name'] ?? '—';

        return [
            'period' => $period,
            'rows' => $rows,
            'company_avg' => $companyAvg,
            'online_count' => $onlineCount,
            'total_employees' => $employees->count(),
            'at_risk_count' => $atRisk,
            'top_department' => $topDept,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetail(int $companyId, int $departmentId, string $period): array
    {
        $department = Team::where('company_id', $companyId)->findOrFail($departmentId);
        $scores = $this->scoreService->getScoresList($companyId, $period, $departmentId);

        $weeklyTrend = $this->buildWeeklyTrend($companyId, $departmentId);
        $userIds = $this->scoreService->getEmployees($companyId, $departmentId)->pluck('id');
        $headcount = max(1, $userIds->count());
        $deptApps = $this->activityUsageService->getDepartmentUsage(
            $userIds,
            $headcount,
            $period,
            10,
            ActivityUsageService::KIND_APPS
        );
        $deptWebsites = $this->activityUsageService->getDepartmentUsage(
            $userIds,
            $headcount,
            $period,
            10,
            ActivityUsageService::KIND_WEBSITES
        );

        return [
            'department' => $department,
            'period' => $period,
            'avg_score' => $scores['team_avg_score'],
            'scores' => $scores,
            'weekly_trend' => $weeklyTrend,
            'dept_apps' => $deptApps,
            'dept_websites' => $deptWebsites,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWeeklyTrend(int $companyId, int $departmentId): array
    {
        $employees = $this->scoreService->getEmployees($companyId, $departmentId)->pluck('id');
        $start = Carbon::today()->subWeeks(11)->startOfWeek(Carbon::MONDAY);
        $end = Carbon::today();

        $summaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $employees)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($s) => $s->date->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));

        $weeks = [];

        foreach (CarbonPeriod::create($start, '1 week', $end) as $weekStart) {
            $key = $weekStart->format('Y-m-d');
            $weekRows = $summaries->get($key, collect());
            $score = $weekRows->isNotEmpty() ? round($weekRows->avg('avg_activity_pct'), 1) : 0;

            $weeks[] = [
                'label' => $weekStart->format('M j'),
                'score' => $score,
            ];
        }

        return $weeks;
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function countAtRisk(int $companyId, array $userIds): int
    {
        if ($userIds === []) {
            return 0;
        }

        $start = Carbon::today()->subDays(6)->toDateString();
        $end = Carbon::today()->toDateString();

        $summaries = AgentDailySummary::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $atRisk = 0;

        foreach ($summaries as $userSummaries) {
            $lowDays = $userSummaries->where('avg_activity_pct', '<', 50)->count();

            if ($lowDays >= 3) {
                $atRisk++;
            }
        }

        return $atRisk;
    }
}
