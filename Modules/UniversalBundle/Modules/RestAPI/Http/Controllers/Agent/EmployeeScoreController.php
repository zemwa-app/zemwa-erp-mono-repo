<?php

namespace Modules\RestAPI\Http\Controllers\Agent;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RestAPI\Entities\AgentActivityWindow;

class EmployeeScoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:90',
        ]);

        $user = $request->user();
        $days = (int) $request->query('days', 30);
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        $windows = AgentActivityWindow::where('user_id', $user->id)
            ->whereBetween('window_start', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get()
            ->groupBy(function ($window) {
                return $window->window_start->format('Y-m-d');
            });

        $scores = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayWindows = $windows->get($dateKey);

            if (!$dayWindows || $dayWindows->isEmpty()) {
                $scores[] = [
                    'date' => $dateKey,
                    'score' => 0,
                    'active_seconds' => 0,
                    'idle_seconds' => 0,
                ];
                continue;
            }

            $nonIdleWindows = $dayWindows->where('is_idle', false);
            $score = $nonIdleWindows->isNotEmpty()
                ? round($nonIdleWindows->avg('activity_pct'), 1)
                : 0;

            $activeSeconds = $nonIdleWindows->sum(function ($w) {
                return $w->window_start->diffInSeconds($w->window_end);
            });

            $idleSeconds = $dayWindows->where('is_idle', true)->sum(function ($w) {
                return $w->window_start->diffInSeconds($w->window_end);
            });

            $scores[] = [
                'date' => $dateKey,
                'score' => $score,
                'active_seconds' => (int) $activeSeconds,
                'idle_seconds' => (int) $idleSeconds,
            ];
        }

        return response()->json(['scores' => $scores]);
    }
}
