<?php

namespace Modules\Monitor\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\RestAPI\Entities\AgentActivityLog;

class ActivityUsageService
{
    public const KIND_ALL = 'all';

    public const KIND_APPS = 'apps';

    public const KIND_WEBSITES = 'websites';

    public function __construct(
        private readonly LogoService $logoService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getEmployeeUsage(int $userId, string $period, int $limit = 10, string $kind = self::KIND_ALL): array
    {
        [$start, $end] = PeriodHelper::resolve($period);
        $logs = $this->fetchLogs([$userId], $start, $end, $kind);

        return $this->buildGroupedUsage($logs, $limit);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<string, mixed>
     */
    public function getDepartmentUsage(
        Collection $userIds,
        int $headcount,
        string $period,
        int $limit = 10,
        string $kind = self::KIND_ALL,
    ): array {
        [$start, $end] = PeriodHelper::resolve($period);
        $logs = $this->fetchLogs($userIds->all(), $start, $end, $kind);

        $items = $this->aggregateLogs($logs, true, $headcount);
        $maxSeconds = max(1, (int) collect($items)->max('total_seconds'));

        foreach ($items as &$item) {
            $item['bar_pct'] = (int) round(($item['total_seconds'] / $maxSeconds) * 100);
            $item['show_warning'] = $item['category'] === 'unproductive'
                && $headcount > 0
                && ($item['employee_count'] / $headcount) > 0.2;
        }

        return [
            'items' => array_slice($items, 0, $limit),
            'total_count' => count($items),
            'max_seconds' => $maxSeconds,
        ];
    }

    /**
     * @param  array<int, int>  $userIds
     * @return array<string, mixed>
     */
    public function getBrowsingSummary(array $userIds, string $period): array
    {
        [$start, $end] = PeriodHelper::resolve($period);
        $allLogs = $this->fetchLogs($userIds, $start, $end, self::KIND_ALL);
        $webLogs = $this->filterLogsByKind($allLogs, self::KIND_WEBSITES);

        $totalSeconds = (int) $allLogs->sum('duration_seconds');
        $webSeconds = (int) $webLogs->sum('duration_seconds');
        $uniqueDomains = $webLogs
            ->map(fn ($log) => $this->logoService->extractDomain($log->url))
            ->filter()
            ->unique()
            ->count();

        return [
            'web_seconds' => $webSeconds,
            'web_label' => MonitorAnalyticsHelper::formatDuration($webSeconds),
            'total_seconds' => $totalSeconds,
            'pct_of_tracked' => $totalSeconds > 0 ? round(($webSeconds / $totalSeconds) * 100, 1) : 0,
            'unique_domains' => $uniqueDomains,
        ];
    }

    /**
     * @param  array<int, int>|null  $userIds
     * @return array<int, array<string, mixed>>
     */
    public function getTopUnproductiveOrg(
        int $companyId,
        string $period,
        int $limit = 3,
        bool $websitesOnly = false,
        ?array $userIds = null,
    ): array {
        [$start, $end] = PeriodHelper::resolve($period);
        $baseQuery = AgentActivityLog::query()
            ->where('company_id', $companyId)
            ->where('category', 'unproductive')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('started_at', [$start, $end])
                    ->orWhereBetween('ended_at', [$start, $end]);
            });

        if ($userIds !== null) {
            if ($userIds === []) {
                return [];
            }

            $baseQuery->whereIn('user_id', $userIds);
        }

        if ($websitesOnly) {
            $baseQuery->whereNotNull('url')->where('url', '!=', '');
        }

        $totalQuery = AgentActivityLog::query()
            ->where('company_id', $companyId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('started_at', [$start, $end])
                    ->orWhereBetween('ended_at', [$start, $end]);
            });

        if ($userIds !== null) {
            $totalQuery->whereIn('user_id', $userIds);
        }

        if ($websitesOnly) {
            $totalQuery->whereNotNull('url')->where('url', '!=', '');
        }

        $totalTrackedSeconds = max(1, (int) $totalQuery->sum('duration_seconds'));
        $logs = $baseQuery->get();
        $items = $this->aggregateLogs($logs, false, 0);

        return collect($items)
            ->take($limit)
            ->map(function (array $item) use ($totalTrackedSeconds) {
                $item['pct_of_tracked'] = round(($item['total_seconds'] / $totalTrackedSeconds) * 100, 1);
                $item['rules_url'] = route('monitor.config.rules.index', [
                    'search' => $item['pattern'],
                    'type' => $item['type'] === 'url' ? 'url' : 'app',
                ]);

                return $item;
            })
            ->all();
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function fetchLogs(array $userIds, Carbon $start, Carbon $end, string $kind = self::KIND_ALL): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        $logs = AgentActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('started_at', [$start, $end])
                    ->orWhereBetween('ended_at', [$start, $end]);
            })
            ->get();

        return $this->filterLogsByKind($logs, $kind);
    }

    private function filterLogsByKind(Collection $logs, string $kind): Collection
    {
        if ($kind === self::KIND_ALL) {
            return $logs;
        }

        if ($kind === self::KIND_WEBSITES) {
            return $logs->filter(fn ($log) => $this->logHasUrl($log))->values();
        }

        return $logs->filter(fn ($log) => !$this->logHasUrl($log))->values();
    }

    private function logHasUrl(AgentActivityLog $log): bool
    {
        return $log->url !== null && trim($log->url) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGroupedUsage(Collection $logs, int $limit): array
    {
        $items = $this->aggregateLogs($logs, false, 0);
        $maxSeconds = max(1, (int) collect($items)->max('total_seconds'));

        foreach ($items as &$item) {
            $item['bar_pct'] = (int) round(($item['total_seconds'] / $maxSeconds) * 100);
        }

        $grouped = [
            'productive' => [],
            'neutral' => [],
            'unproductive' => [],
            'uncategorised' => [],
        ];

        $sectionMeta = [];

        foreach ($items as $item) {
            $bucket = $item['category'] ?? 'uncategorised';

            if (!isset($grouped[$bucket])) {
                $bucket = 'uncategorised';
            }

            $grouped[$bucket][] = $item;
            $sectionMeta[$bucket] = ($sectionMeta[$bucket] ?? 0) + $item['total_seconds'];
        }

        foreach ($grouped as $key => $sectionItems) {
            $grouped[$key] = array_slice($sectionItems, 0, $limit);
            $sectionMeta[$key . '_label'] = MonitorAnalyticsHelper::formatDuration((int) ($sectionMeta[$key] ?? 0));
            $sectionMeta[$key . '_count'] = count($sectionItems);
        }

        return [
            'sections' => $grouped,
            'section_meta' => $sectionMeta,
            'items' => $items,
            'total_count' => count($items),
            'max_seconds' => $maxSeconds,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function aggregateLogs(Collection $logs, bool $withEmployeeCounts, int $headcount): array
    {
        $grouped = [];

        foreach ($logs as $log) {
            $isUrl = $this->logHasUrl($log);
            $domain = $isUrl ? $this->logoService->extractDomain($log->url) : null;
            $key = $isUrl
                ? 'url:' . ($domain ?? 'unknown')
                : 'app:' . strtolower($log->process_name ?? $log->app_name ?? 'unknown');

            if (!isset($grouped[$key])) {
                $label = $this->logoService->displayLabel($log->url, $log->app_name, $log->process_name);
                $icons = $this->logoService->resolveForActivityLog($log->url, $log->app_name, $log->process_name);
                $grouped[$key] = [
                    'key' => $key,
                    'pattern' => $isUrl ? $domain : ($log->process_name ?? $log->app_name),
                    'display_name' => $label,
                    'type' => $isUrl ? 'url' : 'app',
                    'category' => $log->category,
                    'subcategory' => $log->subcategory,
                    'subcategory_label' => $log->subcategory ? ucfirst(str_replace('_', ' ', $log->subcategory)) : null,
                    'total_seconds' => 0,
                    'session_count' => 0,
                    'employee_ids' => [],
                    'icon_url' => $icons['icon_url'],
                    'letter_avatar' => $icons['letter_avatar'],
                ];
            }

            $grouped[$key]['total_seconds'] += (int) $log->duration_seconds;
            $grouped[$key]['session_count']++;

            if ($withEmployeeCounts) {
                $grouped[$key]['employee_ids'][$log->user_id] = true;
            }
        }

        return collect($grouped)
            ->map(function (array $row) use ($withEmployeeCounts, $headcount) {
                $row['duration_label'] = MonitorAnalyticsHelper::formatDuration((int) $row['total_seconds']);

                if ($withEmployeeCounts) {
                    $count = count($row['employee_ids']);
                    $row['employee_count'] = $count;
                    $row['employee_ratio_label'] = $count . ' / ' . max(1, $headcount);
                }

                if (empty($row['category']) || !in_array($row['category'], ['productive', 'neutral', 'unproductive'], true)) {
                    $row['rules_url'] = route('monitor.config.rules.index', [
                        'tab' => 'uncategorised',
                        'search' => $row['pattern'],
                    ]);
                }

                return $row;
            })
            ->sortByDesc('total_seconds')
            ->values()
            ->all();
    }
}
