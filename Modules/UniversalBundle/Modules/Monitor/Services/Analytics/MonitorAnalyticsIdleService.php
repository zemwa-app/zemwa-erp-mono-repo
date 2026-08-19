<?php

namespace Modules\Monitor\Services\Analytics;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Modules\Monitor\Entities\AgentDailySummary;
use Modules\RestAPI\Entities\AgentActivityWindow;
use Modules\RestAPI\Entities\AgentPause;

class MonitorAnalyticsIdleService
{
    /**
     * @return array<string, mixed>
     */
    public function getIdleDetail(int $companyId, int $userId, Carbon $date, bool $showAnomalies): array
    {
        $employee = User::findOrFail($userId);
        $dayStart = $date->copy()->timezone(company()->timezone)->startOfDay();
        $dayEnd = $dayStart->copy()->endOfDay();

        $windows = AgentActivityWindow::query()
            ->where('user_id', $userId)
            ->where('window_start', '>=', $dayStart)
            ->where('window_start', '<=', $dayEnd)
            ->orderBy('window_start')
            ->get();

        $activeSeconds = (int) $windows->where('is_idle', false)->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
        $idleSeconds = (int) $windows->where('is_idle', true)->sum(fn ($w) => $w->window_start->diffInSeconds($w->window_end));
        $totalSeconds = max($activeSeconds + $idleSeconds, 1);

        $periods = $this->buildIdlePeriods($userId, $windows, $dayStart, $dayEnd);
        $weekSummary = $this->buildWeekSummary($userId, $date);
        $anomalies = $showAnomalies
            ? $this->detectAnomalies($userId, $date, $periods, $activeSeconds, $idleSeconds, $totalSeconds)
            : [];

        return [
            'employee' => $employee,
            'date' => $date->toDateString(),
            'prev_date' => $date->copy()->subDay()->toDateString(),
            'next_date' => $date->copy()->addDay()->toDateString(),
            'active_seconds' => $activeSeconds,
            'idle_seconds' => $idleSeconds,
            'active_pct' => round(($activeSeconds / $totalSeconds) * 100, 1),
            'idle_pct' => round(($idleSeconds / $totalSeconds) * 100, 1),
            'active_label' => MonitorAnalyticsHelper::formatDuration($activeSeconds),
            'idle_label' => MonitorAnalyticsHelper::formatDuration($idleSeconds),
            'total_label' => MonitorAnalyticsHelper::formatDuration($activeSeconds + $idleSeconds),
            'periods' => $periods,
            'week_summary' => $weekSummary,
            'anomalies' => $anomalies,
        ];
    }

    /**
     * @param  Collection<int, AgentActivityWindow>  $windows
     * @return array<int, array<string, mixed>>
     */
    private function buildIdlePeriods(int $userId, Collection $windows, Carbon $dayStart, Carbon $dayEnd): array
    {
        $idleWindows = $windows->where('is_idle', true)->values();
        $pauses = AgentPause::query()
            ->where('user_id', $userId)
            ->where('started_at', '>=', $dayStart)
            ->where('started_at', '<=', $dayEnd)
            ->get();

        $periods = [];
        $timeFormat = company()->time_format;

        foreach ($idleWindows as $window) {
            $start = $window->window_start;
            $end = $window->window_end;
            $seconds = $start->diffInSeconds($end);
            $label = $this->classifyIdlePeriod($start, $end, $seconds, $pauses);

            $periods[] = [
                'start' => $start->timezone(company()->timezone)->format($timeFormat),
                'end' => $end->timezone(company()->timezone)->format($timeFormat),
                'duration' => MonitorAnalyticsHelper::formatDuration($seconds),
                'label' => $label['text'],
                'label_class' => $label['class'],
            ];
        }

        return $periods;
    }

    /**
     * @param  Collection<int, AgentPause>  $pauses
     * @return array{text: string, class: string}
     */
    private function classifyIdlePeriod(Carbon $start, Carbon $end, int $seconds, Collection $pauses): array
    {
        foreach ($pauses as $pause) {
            if ($start->lte($pause->started_at) && $end->gte($pause->started_at)) {
                return [
                    'text' => $pause->reason ?: __('monitor::app.events.noReason'),
                    'class' => 'bg-gray-100 text-gray-700',
                ];
            }
        }

        $minutes = $seconds / 60;
        $lunchStart = $start->copy()->setTime(12, 0);
        $lunchEnd = $start->copy()->setTime(13, 30);
        $overlapsLunch = $start->lt($lunchEnd) && $end->gt($lunchStart);

        if ($overlapsLunch && $minutes >= 20 && $minutes <= 90) {
            return ['text' => __('monitor::app.idleLunch'), 'class' => 'bg-blue-100 text-blue-800'];
        }

        if ($minutes < 20) {
            return ['text' => __('monitor::app.idleShortBreak'), 'class' => 'bg-gray-100 text-gray-700'];
        }

        if ($minutes > 60) {
            return ['text' => __('monitor::app.idleExtended'), 'class' => 'bg-red-100 text-red-800'];
        }

        if ($minutes >= 20) {
            return ['text' => __('monitor::app.idleBreak'), 'class' => 'bg-gray-100 text-gray-700'];
        }

        return ['text' => __('monitor::app.idle'), 'class' => 'bg-gray-100 text-gray-700'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWeekSummary(int $userId, Carbon $date): array
    {
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $summaries = AgentDailySummary::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$weekStart->toDateString(), $date->toDateString()])
            ->get()
            ->keyBy(fn ($s) => $s->date->format('Y-m-d'));

        $days = [];

        foreach (CarbonPeriod::create($weekStart, $weekStart->copy()->addDays(6)) as $day) {
            $key = $day->format('Y-m-d');
            $summary = $summaries->get($key);
            $total = ($summary?->active_seconds ?? 0) + ($summary?->idle_seconds ?? 0);
            $activePct = $total > 0 ? round((($summary?->active_seconds ?? 0) / $total) * 100, 0) : null;

            $days[] = [
                'label' => $day->format('D'),
                'date' => $key,
                'active_pct' => $activePct,
                'is_today' => $key === $date->toDateString(),
            ];
        }

        return $days;
    }

    /**
     * @param  array<int, array<string, mixed>>  $periods
     * @return array<int, string>
     */
    private function detectAnomalies(int $userId, Carbon $date, array $periods, int $activeSeconds, int $idleSeconds, int $totalSeconds): array
    {
        $messages = [];
        $idlePct = $totalSeconds > 0 ? ($idleSeconds / $totalSeconds) * 100 : 0;

        if ($idlePct > 40) {
            $messages[] = __('monitor::app.anomalyHighIdleDay', ['pct' => round($idlePct)]);
        }

        foreach ($periods as $period) {
            if (($period['label'] ?? '') === __('monitor::app.idleExtended')) {
                $messages[] = __('monitor::app.anomalyExtendedIdle');
                break;
            }
        }

        $lowDays = AgentDailySummary::query()
            ->where('user_id', $userId)
            ->where('date', '>=', $date->copy()->subDays(6)->toDateString())
            ->where('date', '<=', $date->toDateString())
            ->get()
            ->filter(function ($summary) {
                $total = $summary->active_seconds + $summary->idle_seconds;

                return $total > 0 && (($summary->idle_seconds / $total) * 100) > 35;
            })
            ->count();

        if ($lowDays >= 3) {
            $messages[] = __('monitor::app.anomalyConsecutiveIdle');
        }

        return array_values(array_unique($messages));
    }
}
