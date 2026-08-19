<?php

namespace Modules\Monitor\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Monitor\Entities\ProductivityRule;
use Modules\RestAPI\Entities\AgentActivityLog;

class ProductivityClassifierService
{
    public const GLOBAL_PRIORITY = 10;

    public const ORG_PRIORITY = 100;

    public function __construct(
        private readonly LogoService $logoService,
    ) {
    }

    public static function markRulesChanged(int $companyId): void
    {
        Cache::put(self::rulesChangedCacheKey($companyId), now()->timestamp, now()->addYear());
    }

    public static function rulesChangedCacheKey(int $companyId): string
    {
        return 'monitor_productivity_rules_changed_' . $companyId;
    }

    /**
     * @return Collection<int, ProductivityRule>
     */
    public function loadRulesForCompany(int $companyId): Collection
    {
        return ProductivityRule::query()
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->orderByRaw('CASE WHEN company_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{category: string, subcategory: string, rule_id: int}|null
     */
    public function classifyUrl(string $domain, Collection $rules): ?array
    {
        $domain = $this->logoService->normalizeDomain($domain);

        foreach ($rules as $rule) {
            if ($rule->type !== ProductivityRule::TYPE_URL) {
                continue;
            }

            if ($this->patternMatchesDomain($rule->pattern, $domain)) {
                return [
                    'category' => $rule->category,
                    'subcategory' => $rule->subcategory,
                    'rule_id' => $rule->id,
                ];
            }
        }

        return null;
    }

    /**
     * @return array{category: string, subcategory: string, rule_id: int}|null
     */
    public function classifyApp(?string $processName, Collection $rules): ?array
    {
        if (!$processName) {
            return null;
        }

        $normalized = strtolower(trim($processName));

        foreach ($rules as $rule) {
            if ($rule->type !== ProductivityRule::TYPE_APP) {
                continue;
            }

            if ($this->patternMatchesProcess($rule->pattern, $normalized)) {
                return [
                    'category' => $rule->category,
                    'subcategory' => $rule->subcategory,
                    'rule_id' => $rule->id,
                ];
            }
        }

        return null;
    }

    /**
     * @return array{classified: int, uncategorised: int}
     */
    public function classifyCompanyLogs(int $companyId, ?Carbon $since = null): array
    {
        $since = ($since ?? now()->subDays(30))->copy()->startOfDay();
        $rules = $this->loadRulesForCompany($companyId);
        $rulesChangedAt = Cache::get(self::rulesChangedCacheKey($companyId));

        $classified = 0;
        $matchIncrements = [];

        $query = AgentActivityLog::query()
            ->where('company_id', $companyId)
            ->where('started_at', '>=', $since)
            ->where(function ($q) use ($rulesChangedAt) {
                $q->whereNull('classified_at');

                if ($rulesChangedAt) {
                    $q->orWhere('classified_at', '<', Carbon::createFromTimestamp((int) $rulesChangedAt));
                }
            });

        $query->orderBy('id')->chunkById(500, function ($logs) use ($rules, &$classified, &$matchIncrements) {
            $now = now();
            $buckets = [];

            foreach ($logs as $log) {
                $match = $this->classifyLog($log, $rules);
                $bucketKey = $match
                    ? 'm:' . $match['rule_id'] . ':' . $match['category'] . ':' . $match['subcategory']
                    : 'uncategorised';

                if (!isset($buckets[$bucketKey])) {
                    $buckets[$bucketKey] = [
                        'ids' => [],
                        'match' => $match,
                    ];
                }

                $buckets[$bucketKey]['ids'][] = $log->id;
            }

            foreach ($buckets as $bucket) {
                $ids = $bucket['ids'];
                $match = $bucket['match'];

                if ($match) {
                    AgentActivityLog::query()->whereIn('id', $ids)->update([
                        'category' => $match['category'],
                        'subcategory' => $match['subcategory'],
                        'classified_at' => $now,
                    ]);
                    $classified += count($ids);
                    $matchIncrements[$match['rule_id']] = ($matchIncrements[$match['rule_id']] ?? 0) + count($ids);
                } else {
                    AgentActivityLog::query()->whereIn('id', $ids)->update([
                        'category' => null,
                        'subcategory' => null,
                        'classified_at' => $now,
                    ]);
                }
            }
        });

        foreach ($matchIncrements as $ruleId => $increment) {
            ProductivityRule::where('id', $ruleId)->increment('match_count', $increment);
        }

        $uncategorised = (int) AgentActivityLog::query()
            ->where('company_id', $companyId)
            ->where('started_at', '>=', $since)
            ->whereNull('category')
            ->count();

        return ['classified' => $classified, 'uncategorised' => $uncategorised];
    }

    /**
     * @return array{category: string, subcategory: string, rule_id: int}|null
     */
    public function classifyLog(AgentActivityLog $log, Collection $rules): ?array
    {
        if ($log->url) {
            $domain = $this->logoService->extractDomain($log->url);

            if ($domain) {
                return $this->classifyUrl($domain, $rules);
            }
        }

        if ($log->process_name) {
            return $this->classifyApp($log->process_name, $rules);
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getUncategorisedSummary(int $companyId, int $days = 30): array
    {
        $since = now()->subDays($days)->startOfDay();

        $rows = AgentActivityLog::query()
            ->where('company_id', $companyId)
            ->where('started_at', '>=', $since)
            ->whereNull('category')
            ->get();

        $grouped = [];

        foreach ($rows as $log) {
            $isUrl = (bool) $log->url;
            $key = $isUrl
                ? 'url:' . ($this->logoService->extractDomain($log->url) ?? 'unknown')
                : 'app:' . strtolower($log->process_name ?? $log->app_name ?? 'unknown');

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'pattern' => $isUrl ? $this->logoService->extractDomain($log->url) : ($log->process_name ?? $log->app_name),
                    'type' => $isUrl ? ProductivityRule::TYPE_URL : ProductivityRule::TYPE_APP,
                    'total_seconds' => 0,
                    'employee_ids' => [],
                ];
            }

            $grouped[$key]['total_seconds'] += (int) $log->duration_seconds;
            $grouped[$key]['employee_ids'][$log->user_id] = true;
        }

        return collect($grouped)
            ->map(fn ($row) => array_merge($row, [
                'employee_count' => count($row['employee_ids']),
                'duration_label' => MonitorAnalyticsHelper::formatDuration((int) $row['total_seconds']),
            ]))
            ->sortByDesc('total_seconds')
            ->values()
            ->all();
    }

    private function patternMatchesDomain(string $pattern, string $domain): bool
    {
        $pattern = $this->logoService->normalizeDomain($pattern);

        return $domain === $pattern || str_ends_with($domain, '.' . $pattern);
    }

    private function patternMatchesProcess(string $pattern, string $processName): bool
    {
        $pattern = strtolower(trim($pattern));
        $pattern = preg_replace('/\.exe$/i', '', $pattern) ?? $pattern;

        return $processName === $pattern
            || str_contains($processName, $pattern)
            || str_contains($pattern, $processName);
    }
}
