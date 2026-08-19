<?php

namespace Modules\Monitor\Services\Billing;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonitorBillableSeatService
{
    public function minActiveDays(): int
    {
        return (int) config('monitor.billing.min_active_days', 3);
    }

    public function minTrackedSeconds(): int
    {
        return (int) config('monitor.billing.min_tracked_seconds', 1800);
    }

    /**
     * Employees billable for Monitor in the billing month:
     * - agent heartbeats on at least N separate days, OR
     * - at least M seconds of tracked app time in agent_activity_logs.
     *
     * @return Collection<int, int> user IDs
     */
    public function billableUserIdsForMonth(int $companyId, Carbon $monthStart, Carbon $monthEnd): Collection
    {
        $rangeStart = $monthStart->copy()->startOfDay();
        $rangeEnd = $monthEnd->copy()->endOfDay();

        $minDays = $this->minActiveDays();
        $minSeconds = $this->minTrackedSeconds();

        $fromHeartbeats = DB::table('agent_heartbeats')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT DATE(created_at)) >= ?', [$minDays])
            ->pluck('user_id');

        $fromActivity = DB::table('agent_activity_logs')
            ->where('company_id', $companyId)
            ->whereBetween('started_at', [$rangeStart, $rangeEnd])
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COALESCE(SUM(duration_seconds), 0) >= ?', [$minSeconds])
            ->pluck('user_id');

        return $fromHeartbeats->merge($fromActivity)->unique()->values();
    }

    public function countBillableSeatsForMonth(int $companyId, Carbon $monthStart, Carbon $monthEnd): int
    {
        return $this->billableUserIdsForMonth($companyId, $monthStart, $monthEnd)->count();
    }
}
