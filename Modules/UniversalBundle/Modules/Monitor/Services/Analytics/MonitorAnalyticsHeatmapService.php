<?php

namespace Modules\Monitor\Services\Analytics;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\RestAPI\Entities\AgentActivityWindow;

class MonitorAnalyticsHeatmapService
{
    public const HOUR_START = 7;

    public const HOUR_END = 21;

    /**
     * @return array<string, mixed>
     */
    public function getHeatmap(int $userId, int $days = 90): array
    {
        $days = in_array($days, [30, 60, 90], true) ? $days : 90;

        return Cache::remember("monitor:heatmap:{$userId}:{$days}", now()->addHours(6), function () use ($userId, $days) {
            return $this->buildHeatmap($userId, $days);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildHeatmap(int $userId, int $days): array
    {
        $employee = User::findOrFail($userId);
        $start = Carbon::today()->subDays($days - 1)->startOfDay();
        $end = Carbon::today()->endOfDay();

        $windows = AgentActivityWindow::query()
            ->where('user_id', $userId)
            ->where('is_idle', false)
            ->where('window_start', '>=', $start)
            ->where('window_start', '<=', $end)
            ->get();

        $cells = [];
        $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $buckets = [];

        foreach ($windows as $window) {
            $dow = (int) $window->window_start->dayOfWeekIso - 1;
            $hour = (int) $window->window_start->format('G');

            if ($hour < self::HOUR_START || $hour > self::HOUR_END) {
                continue;
            }

            $key = "{$dow}-{$hour}";

            if (!isset($buckets[$key])) {
                $buckets[$key] = ['scores' => [], 'samples' => 0];
            }

            $buckets[$key]['scores'][] = (float) $window->activity_pct;
            $buckets[$key]['samples']++;
        }

        $peaks = [];

        for ($dow = 0; $dow < 7; $dow++) {
            for ($hour = self::HOUR_START; $hour <= self::HOUR_END; $hour++) {
                $key = "{$dow}-{$hour}";
                $bucket = $buckets[$key] ?? ['scores' => [], 'samples' => 0];
                $avg = !empty($bucket['scores']) ? round(array_sum($bucket['scores']) / count($bucket['scores']), 1) : null;
                $samples = $bucket['samples'];

                $cell = [
                    'dow' => $dow,
                    'hour' => $hour,
                    'hour_label' => sprintf('%02d:00', $hour),
                    'day_label' => $dayLabels[$dow],
                    'avg_score' => $avg,
                    'samples' => $samples,
                    'cell_class' => MonitorAnalyticsHelper::heatmapCellClass($avg, $samples),
                    'tooltip' => $avg !== null
                        ? "{$dayLabels[$dow]} {$hour}:00 · {$avg}% · {$samples} samples"
                        : "{$dayLabels[$dow]} {$hour}:00 · no data",
                ];

                $cells[$key] = $cell;

                if ($samples >= 10 && $avg !== null) {
                    $peaks[] = $cell;
                }
            }
        }

        usort($peaks, fn ($a, $b) => ($b['avg_score'] ?? 0) <=> ($a['avg_score'] ?? 0));
        $peaks = array_slice($peaks, 0, 3);

        $distinctDays = $windows->groupBy(fn ($w) => $w->window_start->format('Y-m-d'))->count();

        return [
            'employee' => $employee,
            'days' => $days,
            'cells' => $cells,
            'day_labels' => $dayLabels,
            'hours' => range(self::HOUR_START, self::HOUR_END),
            'peaks' => $peaks,
            'has_enough_data' => $distinctDays >= 7,
        ];
    }
}
