<?php

namespace Modules\Monitor\Services\Analytics;

class MonitorAnalyticsHelper
{
    public static function scoreTier(?float $score): string
    {
        $score = (float) ($score ?? 0);

        if ($score >= 80) {
            return 'green';
        }

        if ($score >= 60) {
            return 'yellow';
        }

        if ($score >= 40) {
            return 'orange';
        }

        return 'red';
    }

    public static function scoreBarClass(?float $score): string
    {
        return match (self::scoreTier($score)) {
            'green' => 'bg-green-500',
            'yellow' => 'bg-yellow-500',
            'orange' => 'bg-orange-500',
            default => 'bg-red-500',
        };
    }

    public static function scoreBadgeClass(?float $score): string
    {
        return match (self::scoreTier($score)) {
            'green' => 'bg-green-100 text-green-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'orange' => 'bg-orange-100 text-orange-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    public static function scoreTextClass(?float $score): string
    {
        return match (self::scoreTier($score)) {
            'green' => 'text-green-700',
            'yellow' => 'text-yellow-700',
            'orange' => 'text-orange-700',
            default => 'text-red-700',
        };
    }

    /**
     * @return array{direction: string, pct: float, label: string, class: string}
     */
    public static function trend(?float $current, ?float $previous): array
    {
        $current = (float) ($current ?? 0);
        $previous = (float) ($previous ?? 0);

        if ($previous <= 0) {
            return [
                'direction' => 'flat',
                'pct' => 0,
                'label' => '→ 0%',
                'class' => 'text-gray-500',
            ];
        }

        $delta = round((($current - $previous) / $previous) * 100, 1);

        if ($delta > 2) {
            return [
                'direction' => 'up',
                'pct' => $delta,
                'label' => '↑ ' . abs($delta) . '%',
                'class' => 'text-green-600',
            ];
        }

        if ($delta < -2) {
            return [
                'direction' => 'down',
                'pct' => $delta,
                'label' => '↓ ' . abs($delta) . '%',
                'class' => 'text-red-600',
            ];
        }

        return [
            'direction' => 'flat',
            'pct' => $delta,
            'label' => '→ ' . abs($delta) . '%',
            'class' => 'text-gray-500',
        ];
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

    public static function decimalHours(int $seconds): string
    {
        return number_format($seconds / 3600, 2, '.', '');
    }

    public static function motivationalLabel(?float $score): string
    {
        $score = (float) ($score ?? 0);

        if ($score >= 80) {
            return __('monitor::app.scoreMsgExcellent');
        }

        if ($score >= 60) {
            return __('monitor::app.scoreMsgGood');
        }

        if ($score >= 40) {
            return __('monitor::app.scoreMsgModerate');
        }

        return __('monitor::app.scoreMsgLow');
    }

    public static function productivityCategoryBadgeClass(?string $category): string
    {
        return match ($category) {
            'productive' => 'bg-green-100 text-green-800',
            'unproductive' => 'bg-red-100 text-red-800',
            'neutral' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-200 text-gray-600',
        };
    }

    public static function productivityCategoryLabel(?string $category): string
    {
        return match ($category) {
            'productive' => __('monitor::app.categoryProductive'),
            'unproductive' => __('monitor::app.categoryUnproductive'),
            'neutral' => __('monitor::app.categoryNeutral'),
            default => __('monitor::app.uncategorised'),
        };
    }

    public static function heatmapCellClass(?float $score, int $samples): string
    {
        if ($samples < 5) {
            return 'bg-gray-100 border border-dashed border-gray-300';
        }

        if ($score === null || $score <= 0) {
            return 'bg-gray-200';
        }

        if ($score >= 80) {
            return 'bg-teal-700';
        }

        if ($score >= 60) {
            return 'bg-teal-500';
        }

        if ($score >= 40) {
            return 'bg-teal-300';
        }

        return 'bg-teal-100';
    }
}
