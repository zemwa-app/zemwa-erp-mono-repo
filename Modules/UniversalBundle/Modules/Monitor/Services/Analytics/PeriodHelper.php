<?php

namespace Modules\Monitor\Services\Analytics;

use Carbon\Carbon;

class PeriodHelper
{
    public const TODAY = 'today';

    public const THIS_WEEK = 'this_week';

    public const LAST_7_DAYS = 'last_7_days';

    public const THIS_MONTH = 'this_month';

    public const LAST_30_DAYS = 'last_30_days';

    public const DEFAULT_TEAM = self::THIS_WEEK;

    public const DEFAULT_EMPLOYEE = self::TODAY;

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            self::TODAY => __('monitor::app.periodToday'),
            self::THIS_WEEK => __('monitor::app.periodThisWeek'),
            self::LAST_7_DAYS => __('monitor::app.periodLast7Days'),
            self::THIS_MONTH => __('monitor::app.periodThisMonth'),
            self::LAST_30_DAYS => __('monitor::app.periodLast30Days'),
        ];
    }

    public static function normalize(?string $period, string $default = self::THIS_WEEK): string
    {
        $period = $period ?: $default;

        return array_key_exists($period, self::options()) ? $period : $default;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function resolve(string $period, ?string $timezone = null): array
    {
        $tz = $timezone ?? company()->timezone;
        $now = now($tz);

        return match ($period) {
            self::TODAY => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            self::THIS_WEEK => [
                $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            self::LAST_7_DAYS => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            self::THIS_MONTH => [
                $now->copy()->startOfMonth()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            self::LAST_30_DAYS => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            default => [
                $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function previous(string $period, ?string $timezone = null): array
    {
        [$start, $end] = self::resolve($period, $timezone);
        $days = max(1, $start->diffInDays($end) + 1);

        return [
            $start->copy()->subDays($days)->startOfDay(),
            $end->copy()->subDays($days)->endOfDay(),
        ];
    }

    public static function slugForExport(string $period): string
    {
        return str_replace('_', '-', $period);
    }
}
