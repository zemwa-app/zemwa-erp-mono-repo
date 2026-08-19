<?php

namespace Modules\Monitor\Services\Analytics;

use Carbon\Carbon;
use Modules\Monitor\Entities\AgentSession;
use Modules\Monitor\Support\ListSearch;
use Modules\RestAPI\Entities\AgentActivityLog;

class MonitorAnalyticsComplianceService
{
    public function __construct(
        private readonly MonitorAnalyticsScoreService $scoreService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCompliance(int $companyId, string $search = ''): array
    {
        $threshold = (int) config('monitor.compliance.unproductive_threshold_pct', 15);
        $employees = $this->scoreService->getEmployees($companyId);
        $total = $employees->count();
        $userIds = $employees->pluck('id');

        $activeAgentCount = AgentSession::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->where('last_seen_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $agentCoverage = $total > 0 ? round(($activeAgentCount / $total) * 100, 1) : 0;

        $start = Carbon::today()->subDays(6)->startOfDay();
        $end = Carbon::today()->endOfDay();

        $allLogs = AgentActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('started_at', [$start, $end])
                    ->orWhereBetween('ended_at', [$start, $end]);
            })
            ->get()
            ->groupBy('user_id');

        $acceptableCount = 0;
        $nonCompliant = [];

        foreach ($employees as $employee) {
            $logs = $allLogs->get($employee->id, collect());
            $totalSeconds = (int) $logs->sum('duration_seconds');
            $unproductiveSeconds = (int) $logs->where('category', 'unproductive')->sum('duration_seconds');
            $unproductivePct = $totalSeconds > 0 ? round(($unproductiveSeconds / $totalSeconds) * 100, 1) : 0;

            $hasAgent = AgentSession::where('user_id', $employee->id)
                ->where('last_seen_at', '>=', Carbon::now()->subDays(7))
                ->exists();

            if (!$hasAgent) {
                $nonCompliant[] = [
                    'name' => $employee->name,
                    'issue' => __('monitor::app.complianceNoAgent'),
                    'dimension' => 'coverage',
                    'dimension_label' => __('monitor::app.complianceAgentCoverage'),
                ];
            }

            if ($unproductivePct > $threshold) {
                $nonCompliant[] = [
                    'name' => $employee->name,
                    'issue' => __('monitor::app.complianceUnproductive', ['pct' => $unproductivePct, 'threshold' => $threshold]),
                    'dimension' => 'acceptable_use',
                    'dimension_label' => __('monitor::app.complianceAcceptableUse'),
                ];
            } else {
                $acceptableCount++;
            }
        }

        $acceptableUse = $total > 0 ? round(($acceptableCount / $total) * 100, 1) : 0;
        $composite = round(($agentCoverage + $acceptableUse) / 2, 1);
        $search = ListSearch::normalize($search);

        return [
            'composite' => $composite,
            'agent_coverage' => $agentCoverage,
            'active_agent_count' => $activeAgentCount,
            'total_employees' => $total,
            'acceptable_use' => $acceptableUse,
            'acceptable_count' => $acceptableCount,
            'threshold' => $threshold,
            'non_compliant' => ListSearch::filterRows($nonCompliant, $search, ['name', 'issue']),
            'config_url' => route('monitor.config.index'),
        ];
    }
}
