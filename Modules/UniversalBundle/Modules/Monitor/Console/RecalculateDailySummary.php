<?php

namespace Modules\Monitor\Console;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Modules\Monitor\Entities\AgentDailySummary;
use Modules\Monitor\Entities\AgentSession;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Entities\AgentHeartbeat;

class RecalculateDailySummary extends Command
{
    protected $signature = 'monitor:recalculate-daily-summary {--days=90 : Number of past days to rebuild} {--company= : Limit to company id}';

    protected $description = 'Rebuild agent_daily_summaries and sync agent_sessions from activity windows and heartbeats';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $companyFilter = $this->option('company');

        $companies = User::query()
            ->when($companyFilter, fn ($q) => $q->where('company_id', (int) $companyFilter))
            ->distinct()
            ->pluck('company_id');

        foreach ($companies as $companyId) {
            $this->recalculateCompany((int) $companyId, $days);
        }

        $this->info('Daily summaries recalculated.');

        return self::SUCCESS;
    }

    private function recalculateCompany(int $companyId, int $days): void
    {
        $employees = User::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'))
            ->pluck('id');

        if ($employees->isEmpty()) {
            return;
        }

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        $windows = AgentActivityWindow::query()
            ->where('company_id', $companyId)
            ->whereIn('user_id', $employees)
            ->where('window_start', '>=', $startDate->copy()->startOfDay())
            ->where('window_start', '<=', $endDate->copy()->endOfDay())
            ->get()
            ->groupBy(fn ($w) => $w->user_id . '|' . $w->window_start->format('Y-m-d'));

        foreach ($employees as $userId) {
            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                $key = $userId . '|' . $date->format('Y-m-d');
                $dayWindows = $windows->get($key, collect());
                $nonIdle = $dayWindows->where('is_idle', false);
                $idle = $dayWindows->where('is_idle', true);

                $activeSeconds = (int) $nonIdle->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
                $idleSeconds = (int) $idle->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
                $avgScore = $nonIdle->isNotEmpty() ? round($nonIdle->avg('activity_pct'), 2) : 0;

                AgentDailySummary::updateOrCreate(
                    ['user_id' => $userId, 'date' => $date->format('Y-m-d')],
                    [
                        'company_id' => $companyId,
                        'avg_activity_pct' => $avgScore,
                        'active_seconds' => $activeSeconds,
                        'idle_seconds' => $idleSeconds,
                    ]
                );
            }
        }

        $this->syncSessions($companyId, $employees);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $userIds
     */
    private function syncSessions(int $companyId, $userIds): void
    {
        $latestSub = AgentHeartbeat::query()
            ->selectRaw('user_id, MAX(created_at) as last_at')
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        $heartbeats = AgentHeartbeat::query()
            ->where('company_id', $companyId)
            ->joinSub($latestSub, 'latest', function ($join) {
                $join->on('agent_heartbeats.user_id', '=', 'latest.user_id')
                    ->on('agent_heartbeats.created_at', '=', 'latest.last_at');
            })
            ->get()
            ->keyBy('user_id');

        foreach ($userIds as $userId) {
            $heartbeat = $heartbeats->get($userId);
            $online = $heartbeat && $heartbeat->created_at->diffInMinutes(now()) < 2 && !$heartbeat->is_paused;

            AgentSession::updateOrCreate(
                ['user_id' => $userId],
                [
                    'company_id' => $companyId,
                    'is_online' => $online,
                    'last_seen_at' => $heartbeat?->created_at,
                    'active_app' => $heartbeat?->active_app,
                ]
            );
        }
    }
}
