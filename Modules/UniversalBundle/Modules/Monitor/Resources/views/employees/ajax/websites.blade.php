@php
    $sites = collect($activeWebsites ?? []);
    $dayDate = $selectedDate ?? now(company()->timezone)->toDateString();

    $categoryBadgeClass = function (?string $category): string {
        return match ($category) {
            'productive' => 'badge-success',
            'unproductive' => 'badge-danger',
            'neutral' => 'badge-warning',
            default => 'badge-secondary',
        };
    };

    $classifyWebsiteType = function (array $site): string {
        $haystack = strtolower(implode(' ', array_filter([
            $site['display_name'] ?? '',
            $site['subcategory_label'] ?? '',
            collect($site['sessions'] ?? [])->map(fn ($s) => implode(' ', [
                $s['url'] ?? '',
                $s['window_title'] ?? '',
                $s['browser'] ?? '',
            ]))->implode(' '),
        ])));

        if (str_contains($haystack, 'chatgpt') || str_contains($haystack, 'claude') || str_contains($haystack, 'gemini') || str_contains($haystack, 'perplexity') || str_contains($haystack, 'copilot')) {
            return 'AI Tools';
        }

        if (str_contains($haystack, 'github') || str_contains($haystack, 'gitlab') || str_contains($haystack, 'bitbucket') || str_contains($haystack, 'stackoverflow') || str_contains($haystack, 'stack overflow') || str_contains($haystack, 'vercel') || str_contains($haystack, 'netlify') || str_contains($haystack, 'npm') || str_contains($haystack, 'developer')) {
            return 'Development';
        }

        if (str_contains($haystack, 'docs') || str_contains($haystack, 'documentation') || str_contains($haystack, 'laravel') || str_contains($haystack, 'readthedocs') || str_contains($haystack, 'php.net') || str_contains($haystack, 'mozilla') || str_contains($haystack, 'confluence') || str_contains($haystack, 'notion')) {
            return 'Documentation';
        }

        if (str_contains($haystack, 'google') || str_contains($haystack, 'search') || str_contains($haystack, 'wikipedia') || str_contains($haystack, 'research') || str_contains($haystack, 'academic')) {
            return 'Research';
        }

        if (str_contains($haystack, 'slack') || str_contains($haystack, 'teams') || str_contains($haystack, 'outlook') || str_contains($haystack, 'gmail') || str_contains($haystack, 'mail') || str_contains($haystack, 'zoom') || str_contains($haystack, 'meet') || str_contains($haystack, 'discord') || str_contains($haystack, 'whatsapp')) {
            return 'Communication';
        }

        if (str_contains($haystack, 'youtube') || str_contains($haystack, 'netflix') || str_contains($haystack, 'spotify') || str_contains($haystack, 'twitch') || str_contains($haystack, 'primevideo') || str_contains($haystack, 'disney')) {
            return 'Entertainment';
        }

        if (str_contains($haystack, 'linkedin') || str_contains($haystack, 'x.com') || str_contains($haystack, 'twitter') || str_contains($haystack, 'facebook') || str_contains($haystack, 'instagram') || str_contains($haystack, 'reddit')) {
            return 'Social Media';
        }

        if (str_contains($haystack, 'amazon') || str_contains($haystack, 'flipkart') || str_contains($haystack, 'ebay') || str_contains($haystack, 'shop') || str_contains($haystack, 'store') || str_contains($haystack, 'cart')) {
            return 'Shopping';
        }

        if (str_contains($haystack, 'news') || str_contains($haystack, 'bbc') || str_contains($haystack, 'cnn') || str_contains($haystack, 'reuters') || str_contains($haystack, 'bloomberg') || str_contains($haystack, 'verge')) {
            return 'News';
        }

        return 'Other';
    };

    $websiteRows = $sites->map(function (array $site) use ($classifyWebsiteType) {
        $sessions = collect($site['sessions'] ?? []);
        $urls = $sessions->pluck('url')->filter()->unique()->values();
        $latestSession = $sessions->sortByDesc(fn ($session) => (int) ($session['started_timestamp'] ?? 0))->first() ?? [];
        $websiteType = $classifyWebsiteType($site);

        $site['website_type'] = $websiteType;
        $site['visit_count'] = max(1, (int) ($site['session_count'] ?? $sessions->count()));
        $site['unique_pages'] = max(1, $urls->count());
        $site['last_visit_label'] = $latestSession['ended_at'] ?? $latestSession['started_at'] ?? ($site['last_seen'] ?? '—');
        $site['latest_session_label'] = $latestSession['duration_label'] ?? ($site['duration_label'] ?? '0m');
        $site['search_haystack'] = strtolower(implode(' ', array_filter([
            $site['display_name'] ?? '',
            $site['website_type'] ?? '',
            $site['category_label'] ?? '',
            $site['subcategory_label'] ?? '',
            implode(' ', $urls->all()),
        ])));
        $site['urls'] = $urls->all();

        return $site;
    })->sortByDesc('total_seconds')->values();

    $flatUrls = $websiteRows->flatMap(function (array $site) use ($categoryBadgeClass) {
        return collect($site['sessions'] ?? [])->map(function (array $session) use ($site, $categoryBadgeClass) {
            $url = $session['url'] ?? '';
            $domain = $site['display_name'] ?? (parse_url($url, PHP_URL_HOST) ?: 'unknown');
            $sessionCategory = $session['category'] ?? $site['category'] ?? null;

            return [
                'url' => $url,
                'domain' => $domain,
                'title' => $session['window_title'] ?? '—',
                'browser' => $session['browser'] ?? '—',
                'category_label' => $session['category_label'] ?? ($site['category_label'] ?? 'Neutral'),
                'category_badge_class' => $categoryBadgeClass($sessionCategory),
                'started_at' => $session['started_at'] ?? '—',
                'ended_at' => $session['ended_at'] ?? '—',
                'duration_label' => $session['duration_label'] ?? '0m',
                'duration_seconds' => (int) ($session['duration_seconds'] ?? 0),
                'productivity_label' => $session['category_label'] ?? ($site['category_label'] ?? 'Neutral'),
                'search_haystack' => strtolower(implode(' ', array_filter([
                    $url,
                    $domain,
                    $session['window_title'] ?? '',
                    $session['browser'] ?? '',
                    $session['category_label'] ?? '',
                ]))),
                'started_timestamp' => (int) ($session['started_timestamp'] ?? 0),
            ];
        });
    })->sortBy('started_timestamp')->values();

    $websiteCount = $websiteRows->count();
    $totalBrowsingSeconds = (int) $websiteRows->sum('total_seconds');
    $productiveSeconds = (int) $websiteRows->where('category', 'productive')->sum('total_seconds');
    $neutralSeconds = (int) $websiteRows->where('category', 'neutral')->sum('total_seconds');
    $attentionSeconds = (int) $websiteRows->where('category', 'unproductive')->sum('total_seconds');
    $totalNonProductiveSeconds = max(0, $neutralSeconds + $attentionSeconds);
    $productivePct = $totalBrowsingSeconds > 0 ? round(($productiveSeconds / $totalBrowsingSeconds) * 100, 1) : 0;
    $neutralPct = $totalBrowsingSeconds > 0 ? round(($neutralSeconds / $totalBrowsingSeconds) * 100, 1) : 0;
    $attentionPct = $totalBrowsingSeconds > 0 ? round(($attentionSeconds / $totalBrowsingSeconds) * 100, 1) : 0;

    $websiteScores = [];
    $websiteScores['browsing'] = (int) max(0, min(100, round(58 + ($productivePct * 0.55) + ($neutralPct * 0.15) - ($attentionPct * 0.9))));
    $websiteScores['health'] = (int) max(0, min(100, round($websiteScores['browsing'] - 2 + ($productivePct * 0.05) - ($attentionPct * 0.1))));

    $websiteScoreLabel = match (true) {
        $websiteScores['browsing'] >= 90 => 'Excellent',
        $websiteScores['browsing'] >= 75 => 'Good',
        $websiteScores['browsing'] >= 60 => 'Needs Attention',
        default => 'Critical',
    };

    $websiteScoreTone = match (true) {
        $websiteScores['browsing'] >= 90 => 'green',
        $websiteScores['browsing'] >= 75 => 'emerald',
        $websiteScores['browsing'] >= 60 => 'amber',
        default => 'red',
    };

    $mostVisitedWebsite = $websiteRows->first();
    $currentWebsite = $flatUrls->sortByDesc('started_timestamp')->first();
    $currentWebsiteDomain = $currentWebsite['domain'] ?? ($mostVisitedWebsite['display_name'] ?? '—');
    $currentSessionLabel = $currentWebsite['duration_label'] ?? '0m';
    $researchSeconds = (int) $websiteRows->whereIn('website_type', ['Research', 'Documentation', 'News'])->sum('total_seconds');
    $distractionSeconds = (int) $websiteRows->whereIn('website_type', ['Social Media', 'Entertainment', 'Shopping'])->sum('total_seconds');
    $communicationSeconds = (int) $websiteRows->where('website_type', 'Communication')->sum('total_seconds');
    $aiToolsSeconds = (int) $websiteRows->where('website_type', 'AI Tools')->sum('total_seconds');
    $developmentSeconds = (int) $websiteRows->where('website_type', 'Development')->sum('total_seconds');

    $intentBuckets = [
        ['label' => 'Development Work', 'seconds' => $developmentSeconds + $aiToolsSeconds, 'tone' => 'gray'],
        ['label' => 'Research & Learning', 'seconds' => $researchSeconds, 'tone' => 'emerald'],
        ['label' => 'Documentation', 'seconds' => (int) $websiteRows->where('website_type', 'Documentation')->sum('total_seconds'), 'tone' => 'sky'],
        ['label' => 'Communication', 'seconds' => $communicationSeconds, 'tone' => 'amber'],
        ['label' => 'Distraction', 'seconds' => $distractionSeconds, 'tone' => 'red'],
    ];

    $intentTotal = max(1, array_sum(array_column($intentBuckets, 'seconds')));
    $intentBuckets = collect($intentBuckets)->map(function (array $bucket) use ($intentTotal) {
        $bucket['pct'] = round(($bucket['seconds'] / $intentTotal) * 100, 1);
        $bucket['label_seconds'] = \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($bucket['seconds']);

        return $bucket;
    })->sortByDesc('seconds')->values();

    $categoryBuckets = collect([
        'Development' => $developmentSeconds,
        'Research' => (int) $websiteRows->where('website_type', 'Research')->sum('total_seconds'),
        'Documentation' => (int) $websiteRows->where('website_type', 'Documentation')->sum('total_seconds'),
        'Communication' => $communicationSeconds,
        'AI Tools' => $aiToolsSeconds,
        'Social Media' => (int) $websiteRows->where('website_type', 'Social Media')->sum('total_seconds'),
        'Entertainment' => (int) $websiteRows->where('website_type', 'Entertainment')->sum('total_seconds'),
        'Shopping' => (int) $websiteRows->where('website_type', 'Shopping')->sum('total_seconds'),
        'News' => (int) $websiteRows->where('website_type', 'News')->sum('total_seconds'),
        'Other' => (int) $websiteRows->where('website_type', 'Other')->sum('total_seconds'),
    ])->sortDesc();

    $categoryTotal = max(1, array_sum($categoryBuckets->all()));
    $categoryBuckets = $categoryBuckets->map(function (int $seconds, string $label) use ($categoryTotal) {
        return [
            'label' => $label,
            'seconds' => $seconds,
            'label_seconds' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($seconds),
            'pct' => round(($seconds / $categoryTotal) * 100, 1),
        ];
    })->filter(fn (array $item) => $item['seconds'] > 0)->values();

    $timeline = $flatUrls->take(8)->map(function (array $item) use ($totalBrowsingSeconds) {
        return [
            'time' => $item['started_at'],
            'domain' => $item['domain'],
            'duration_label' => $item['duration_label'],
            'duration_seconds' => $item['duration_seconds'],
            'pct' => $totalBrowsingSeconds > 0 ? round(($item['duration_seconds'] / $totalBrowsingSeconds) * 100, 1) : 0,
            'website_type' => $item['productivity_label'],
        ];
    });

    $positiveSignals = [];

    if ($researchSeconds >= max(20 * 60, (int) ($totalBrowsingSeconds * 0.25))) {
        $positiveSignals[] = ['label' => 'Focused research activity', 'tone' => 'green'];
    }

    if ($developmentSeconds + $aiToolsSeconds >= max(30 * 60, (int) ($totalBrowsingSeconds * 0.35))) {
        $positiveSignals[] = ['label' => 'Development-related browsing dominated', 'tone' => 'green'];
    }

    if ($websiteCount <= 12) {
        $positiveSignals[] = ['label' => 'Low context switching', 'tone' => 'green'];
    }

    if ($websiteRows->where('website_type', 'Documentation')->sum('total_seconds') > 0) {
        $positiveSignals[] = ['label' => 'Strong documentation usage', 'tone' => 'green'];
    }

    $attentionItems = [];

    if (($websiteRows->where('website_type', 'Social Media')->sum('total_seconds')) > 15 * 60) {
        $attentionItems[] = ['label' => 'Excessive social media usage', 'tone' => 'amber'];
    }

    if (($websiteRows->where('website_type', 'Entertainment')->sum('total_seconds')) > 15 * 60) {
        $attentionItems[] = ['label' => 'Repeated entertainment website visits', 'tone' => 'amber'];
    }

    if ($websiteCount > 18) {
        $attentionItems[] = ['label' => 'High tab switching activity', 'tone' => 'amber'];
    }

    if ($attentionSeconds > max(20 * 60, (int) ($totalBrowsingSeconds * 0.2))) {
        $attentionItems[] = ['label' => 'Browsing outside normal patterns', 'tone' => 'amber'];
    }

    $topDomains = $websiteRows->take(4)->pluck('display_name')->filter()->implode(', ');
    $aiSummary = 'No unusual browsing behavior detected.';

    if ($attentionSeconds > max(20 * 60, (int) ($totalBrowsingSeconds * 0.2))) {
        $aiSummary = 'Entertainment-related browsing exceeded normal baseline by ' . \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($attentionSeconds) . '.';
    } elseif ($developmentSeconds + $researchSeconds >= max(30 * 60, (int) ($totalBrowsingSeconds * 0.6))) {
        $aiSummary = 'Browsing activity was heavily focused on development and research websites. ' . ($topDomains !== '' ? $topDomains . ' accounted for a large share of browsing time. ' : '') . 'No unusual browsing behavior detected.';
    } elseif ($topDomains !== '') {
        $aiSummary = 'Browsing activity was concentrated around ' . $topDomains . '. No unusual browsing behavior detected.';
    }

    $websiteHealthTone = $websiteScoreTone;
@endphp

<div class="p-20">
    <div class="row">
        <div class="col-lg-8 mb-3">
            @include('monitor::employees.ajax.partials.website-summary-card', [
                'websiteCount' => $websiteCount,
                'totalBrowsingSeconds' => $totalBrowsingSeconds,
                'productiveSeconds' => $productiveSeconds,
                'neutralSeconds' => $neutralSeconds,
                'attentionSeconds' => $attentionSeconds,
                'mostVisitedWebsite' => $mostVisitedWebsite,
                'browsingScore' => $websiteScores['browsing'],
                'browsingScoreLabel' => $websiteScoreLabel,
                'browsingScoreTone' => $websiteScoreTone,
            ])

            @include('monitor::employees.ajax.partials.ai-summary-card', [
                'aiSummary' => $aiSummary,
                'topDomains' => $topDomains,
                'totalBrowsingSeconds' => $totalBrowsingSeconds,
            ])
        </div>

        <div class="col-lg-4 mb-3">
            @include('monitor::employees.ajax.partials.website-health-widget', [
                'browsingScore' => $websiteScores['browsing'],
                'browsingScoreLabel' => $websiteScoreLabel,
                'websiteHealthScore' => $websiteScores['health'],
                'websiteHealthLabel' => $websiteScoreLabel,
                'websiteHealthTone' => $websiteHealthTone,
                'mostVisitedWebsite' => $mostVisitedWebsite,
                'currentWebsiteDomain' => $currentWebsiteDomain,
                'currentSessionLabel' => $currentSessionLabel,
                'researchSeconds' => $researchSeconds,
                'distractionSeconds' => $distractionSeconds,
                'researchLabel' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($researchSeconds),
                'distractionLabel' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($distractionSeconds),
            ])
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            @include('monitor::employees.ajax.partials.website-cards', [
                'websiteRows' => $websiteRows->take(6),
            ])

            @include('monitor::employees.ajax.partials.work-intent-analysis', [
                'intentBuckets' => $intentBuckets,
            ])

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Browsing Signals</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Insights and attention items from today's browsing activity.</p>
                        </div>
                        <div class="d-flex flex-wrap">
                            <span class="badge badge-success mr-2 mb-1">
                                {{ count($positiveSignals) }} {{ count($positiveSignals) === 1 ? 'insight' : 'insights' }}
                            </span>
                            <span class="badge {{ count($attentionItems) > 0 ? 'badge-warning' : 'badge-secondary' }} mb-1">
                                {{ count($attentionItems) }} {{ count($attentionItems) === 1 ? 'item needs' : 'items need' }} review
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        @include('monitor::employees.ajax.partials.insight-card', [
                            'title' => 'Browsing Insights',
                            'subtitle' => 'What stands out in the browsing pattern',
                            'items' => $positiveSignals,
                            'tone' => 'green',
                            'emptyText' => 'No positive signals available',
                            'borderRight' => true,
                        ])

                        @include('monitor::employees.ajax.partials.insight-card', [
                            'title' => 'Attention Items',
                            'subtitle' => 'What may require a quick review',
                            'items' => $attentionItems,
                            'tone' => 'amber',
                            'emptyText' => 'No attention items detected',
                        ])
                    </div>
                </div>
            </div>

            @include('monitor::employees.ajax.partials.browsing-timeline', [
                'timeline' => $timeline,
                'totalBrowsingSeconds' => $totalBrowsingSeconds,
            ])

            @include('monitor::employees.ajax.partials.domain-intelligence-card', [
                'websiteRows' => $websiteRows,
            ])

            @include('monitor::employees.ajax.partials.detailed-url-table', [
                'flatUrls' => $flatUrls,
            ])
        </div>

        <div class="col-lg-4 mb-3">
            @include('monitor::employees.ajax.partials.category-distribution-card', [
                'categoryBuckets' => $categoryBuckets,
            ])
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            $('body').off('click.monitorWebsiteDomain').on('click.monitorWebsiteDomain', '[data-domain-toggle]', function () {
                const $btn = $(this);
                const $row = $($btn.data('domain-toggle'));
                const expanded = $btn.attr('aria-expanded') === 'true';

                $row.toggleClass('d-none', expanded);
                $btn.attr('aria-expanded', expanded ? 'false' : 'true');
                $btn.find('i').toggleClass('fa-plus', expanded).toggleClass('fa-minus', !expanded);
            });

            $('body').off('click.monitorUrlDetails').on('click.monitorUrlDetails', '[data-url-toggle]', function () {
                const $panel = $('[data-url-panel]').first();
                const expanded = $panel.hasClass('d-none');

                $panel.toggleClass('d-none', !expanded);
                $(this).find('span').text(expanded ? 'Collapse detailed URLs' : 'Expand detailed URLs');
                $(this).find('i').toggleClass('fa-plus', !expanded).toggleClass('fa-minus', expanded);
            });
        });
    </script>
@endpush
