@php
    use Illuminate\Support\Str;

    $rows = collect($networkLogs ?? [])->values();
    $hasData = $rows->isNotEmpty();
    $classifySource = function (string $value): string {
        $haystack = strtolower($value);
        if (Str::contains($haystack, ['codex', 'vscode', 'phpstorm', 'cursor', 'terminal', 'git', 'github desktop', 'gitkraken', 'intellij', 'webstorm', 'pycharm'])) {
            return 'Development';
        }
        if (Str::contains($haystack, ['slack', 'teams', 'discord', 'zoom', 'meet', 'mail', 'gmail', 'outlook', 'whatsapp'])) {
            return 'Communication';
        }
        if (Str::contains($haystack, ['chatgpt', 'claude', 'openai', 'gemini', 'perplexity', 'copilot'])) {
            return 'AI Tools';
        }
        if (Str::contains($haystack, ['docs', 'documentation', 'notion', 'confluence', 'readme', 'wiki'])) {
            return 'Documentation';
        }
        if (Str::contains($haystack, ['drive', 'dropbox', 'onedrive', 's3', 'cloud', 'box', 'upload'])) {
            return 'Cloud Storage';
        }
        if (Str::contains($haystack, ['meeting', 'calendar', 'zoom', 'meet', 'teams'])) {
            return 'Meetings';
        }

        return 'Other';
    };
    $formatBytes = fn (int $bytes) => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatBytes($bytes);
    $totalUploadBytes = (int) $rows->sum('uploaded_bytes');
    $totalDownloadBytes = (int) $rows->sum('downloaded_bytes');
    $totalDataBytes = $totalUploadBytes + $totalDownloadBytes;
    $peakRow = $rows->sortByDesc('total_bytes')->first() ?? [];
    $peakHour = $peakRow['hour_label'] ?? ($rows->sortByDesc('hour_timestamp')->first()['hour_label'] ?? '—');
    $peakHourBytes = (int) ($peakRow['total_bytes'] ?? 0);
    $alertRows = $rows->filter(fn ($row) => !empty($row['has_cloud_alert']) || !empty($row['large_transfer_alert']));
    $alertCount = (int) $alertRows->count();
    $currentRow = $rows->sortByDesc('hour_timestamp')->first() ?? [];
    $currentRowBytes = (int) ($currentRow['total_bytes'] ?? (($currentRow['uploaded_bytes'] ?? 0) + ($currentRow['downloaded_bytes'] ?? 0)));
    $currentNetworkActivity = $currentRowBytes > 0
        ? (($currentRowBytes >= 400 * 1024 * 1024) ? 'Heavy' : (($currentRowBytes >= 100 * 1024 * 1024) ? 'Moderate' : 'Light'))
        : 'Idle';
    $networkScore = $hasData ? (int) max(0, min(100, round(
        100
        - min(30, ($alertCount * 12))
        - (count($rows->filter(fn ($row) => ($row['total_bytes'] ?? 0) >= 400 * 1024 * 1024)) * 8)
        + (count($rows->filter(fn ($row) => ($row['vpn_active'] ?? false))) * 2)
    ))) : 0;
    $networkScoreLabel = match (true) {
        $networkScore >= 90 => 'Normal',
        $networkScore >= 75 => 'Good',
        $networkScore >= 60 => 'Review',
        default => 'Critical',
    };
    $networkTone = match (true) {
        $networkScore >= 90 => 'green',
        $networkScore >= 75 => 'emerald',
        $networkScore >= 60 => 'amber',
        default => 'red',
    };
    $processTotals = $rows
        ->flatMap(function (array $row) {
            return collect($row['top_processes'] ?? [])->map(function ($process) use ($row) {
                $name = $process['process'] ?? 'Unknown';
                $bytes = (int) ($process['bytes_sent'] ?? 0) + (int) ($process['bytes_received'] ?? 0);

                return [
                    'name' => $name,
                    'bytes' => $bytes,
                    'category' => $process['category'] ?? null,
                    'hour_label' => $row['hour_label'] ?? $row['hour'] ?? '—',
                ];
            });
        })
        ->groupBy('name')
        ->map(function ($items, string $name) use ($classifySource) {
            $bytes = (int) collect($items)->sum('bytes');
            $category = collect($items)->pluck('category')->filter()->first() ?: $classifySource($name);

            return [
                'name' => $name,
                'bytes' => $bytes,
                'category' => $category,
                'label' => $bytes > 0 ? $bytes : 0,
            ];
        })
        ->sortByDesc('bytes')
        ->values();
    $topSources = $processTotals->take(5);
    $maxSourceBytes = max(1, (int) ($topSources->max('bytes') ?? 1));
    $serviceBreakdown = collect([
        'Development' => 0,
        'Communication' => 0,
        'AI Tools' => 0,
        'Documentation' => 0,
        'Cloud Storage' => 0,
        'Meetings' => 0,
        'Other' => 0,
    ]);
    foreach ($rows as $row) {
        foreach (($row['top_processes'] ?? []) as $process) {
            $name = (string) ($process['process'] ?? 'Unknown');
            $category = $classifySource($name);
            $serviceBreakdown[$category] = ($serviceBreakdown[$category] ?? 0) + (int) ($process['bytes_sent'] ?? 0) + (int) ($process['bytes_received'] ?? 0);
        }
        foreach (($row['cloud_uploads'] ?? []) as $cloudUpload) {
            $serviceBreakdown['Cloud Storage'] += max(0, (int) ($row['uploaded_bytes'] ?? 0) / 2);
        }
    }
    $serviceTotal = max(1, (int) $serviceBreakdown->sum());
    $serviceBreakdown = $serviceBreakdown->map(function (int $bytes, string $label) use ($serviceTotal, $formatBytes) {
        return [
            'label' => $label,
            'bytes' => $bytes,
            'value' => $formatBytes($bytes),
            'pct' => round(($bytes / $serviceTotal) * 100, 1),
        ];
    })->filter(fn ($item) => $item['bytes'] > 0)->sortByDesc('bytes')->values();
    $timelineRows = collect(range(7, 23))->map(function (int $hour) use ($rows) {
        $hourRow = $rows->firstWhere('hour_label', \Carbon\Carbon::createFromTime($hour, 0, 0, company()->timezone)->format('g A'));
        $uploaded = (int) ($hourRow['uploaded_bytes'] ?? 0);
        $downloaded = (int) ($hourRow['downloaded_bytes'] ?? 0);
        $total = $uploaded + $downloaded;
        $level = $total === 0 ? 'idle' : ($total >= 400 * 1024 * 1024 ? 'high' : ($total >= 100 * 1024 * 1024 ? 'medium' : 'low'));

        return [
            'hour' => $hour,
            'label' => \Carbon\Carbon::createFromTime($hour, 0, 0, company()->timezone)->format('g A'),
            'uploaded' => $uploaded,
            'downloaded' => $downloaded,
            'total' => $total,
            'level' => $level,
            'row' => $hourRow,
        ];
    });
    $correlations = $rows->map(function (array $row) use ($classifySource, $formatBytes) {
        $topApps = collect($row['top_processes'] ?? [])->pluck('process')->filter()->take(2);
        $topSites = collect($row['cloud_uploads'] ?? [])->filter()->take(2);

        return [
            'hour_label' => $row['hour_label'] ?? ($row['hour'] ?? '—'),
            'apps' => $topApps,
            'sites' => $topSites,
            'total' => $formatBytes((int) ($row['total_bytes'] ?? 0)),
            'activity' => (($row['total_bytes'] ?? 0) >= 400 * 1024 * 1024) ? 'Network spike' : (($row['total_bytes'] ?? 0) >= 100 * 1024 * 1024 ? 'Steady use' : 'Light activity'),
        ];
    })->filter(fn ($item) => $item['total'] !== '0B')->sortByDesc('hour_label')->values();
    $anomalies = collect();
    foreach ($rows as $row) {
        if (!empty($row['large_transfer_alert'])) {
            $anomalies->push([
                'title' => 'Large Upload Detected',
                'detail' => ($row['hour_label'] ?? $row['hour'] ?? '—') . ' · ' . $formatBytes((int) ($row['uploaded_bytes'] ?? 0)),
                'tone' => 'red',
            ]);
        }
        if (!empty($row['has_cloud_alert'])) {
            $anomalies->push([
                'title' => 'Cloud Storage Activity',
                'detail' => implode(', ', collect($row['cloud_uploads'] ?? [])->take(2)->all()) ?: 'Cloud upload detected',
                'tone' => 'amber',
            ]);
        }
    }
    if ($anomalies->isEmpty()) {
        $anomalies->push([
            'title' => 'No alerts found',
            'detail' => 'Network usage stayed within the normal range for the selected day.',
            'tone' => 'green',
        ]);
    }
    $sessions = [];
    $currentSession = null;
    foreach ($rows as $row) {
        $sourceNames = collect($row['top_processes'] ?? [])->pluck('process')->filter()->values();
        $sessionCategory = $classifySource($sourceNames->first() ?: '');
        $totalBytes = (int) ($row['total_bytes'] ?? 0);
        if (!$currentSession) {
            $currentSession = [
                'start' => $row,
                'end' => $row,
                'bytes' => $totalBytes,
                'sources' => $sourceNames,
                'category' => $sessionCategory,
            ];
            continue;
        }
        $currentHour = (int) (\Carbon\Carbon::createFromFormat('g A', $currentSession['end']['hour_label'] ?? '12 AM', company()->timezone)->format('G'));
        $rowHour = (int) (\Carbon\Carbon::createFromFormat('g A', $row['hour_label'] ?? '12 AM', company()->timezone)->format('G'));
        if ($rowHour === $currentHour - 1 || $rowHour === $currentHour + 1) {
            $currentSession['end'] = $row;
            $currentSession['bytes'] += $totalBytes;
            $currentSession['sources'] = $currentSession['sources']->merge($sourceNames)->unique()->values();
            continue;
        }
        $sessions[] = $currentSession;
        $currentSession = [
            'start' => $row,
            'end' => $row,
            'bytes' => $totalBytes,
            'sources' => $sourceNames,
            'category' => $sessionCategory,
        ];
    }
    if ($currentSession) {
        $sessions[] = $currentSession;
    }
    $sessions = collect($sessions)->map(function (array $session) use ($formatBytes) {
        $startLabel = $session['start']['hour_label'] ?? '—';
        $endLabel = $session['end']['hour_label'] ?? '—';

        return [
            'label' => match ($session['category']) {
                'Development' => 'Development Session',
                'Communication' => 'Communication Session',
                'AI Tools' => 'AI Session',
                'Documentation' => 'Documentation Session',
                default => 'Network Session',
            },
            'start_label' => $startLabel,
            'end_label' => $endLabel,
            'bytes_label' => $formatBytes((int) ($session['bytes'] ?? 0)),
            'sources' => collect($session['sources'] ?? [])->take(4)->values(),
            'category' => $session['category'] ?? 'Other',
        ];
    })->values();
    $networkSummary = $hasData
        ? (
            $alertCount > 0
                ? 'Network activity was mostly aligned with work-related tools. Most traffic came from ' . ($topSources->take(3)->pluck('name')->implode(', ') ?: 'core work apps') . ', but ' . $alertCount . ' alert(s) suggest reviewing the heaviest transfer periods.'
                : 'Network activity was consistent with productive work. Most traffic originated from ' . ($topSources->take(3)->pluck('name')->implode(', ') ?: 'core work apps') . '. No unusual transfer patterns were detected.'
        )
        : 'No network activity available for this date.';
    $bandwidthComparison = $totalDataBytes > 0 ? round(($totalUploadBytes / max(1, $totalDataBytes)) * 100, 1) : 0;
    $networkBadgeClass = match ($networkTone) {
        'green', 'emerald' => 'badge-success',
        'amber' => 'badge-warning',
        'red' => 'badge-danger',
        default => 'badge-secondary',
    };
@endphp

<div class="p-20">
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Network Activity Summary</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">A manager-first read on whether network usage looked normal.</p>
                        </div>
                        <span class="badge {{ $networkBadgeClass }}">{{ $networkScoreLabel }}</span>
                    </div>
                </div>
                <div class="card-body p-20">
                    <div class="row">
                        <div class="col-lg-5 col-md-6 mb-3">
                            <div class="border-grey rounded p-3 h-100">
                                <div class="f-11 text-lightest text-uppercase">Network Activity Score</div>
                                <div class="mt-3 d-flex align-items-end justify-content-between">
                                    <div class="f-21 font-weight-bold text-darkest-grey">{{ number_format($networkScore) }}/100</div>
                                    @php
                                        $score = max(0, min(100, $networkScore));
                                        $radius = 22;
                                        $circumference = 2 * pi() * $radius;
                                        $offset = $circumference - (($score / 100) * $circumference);
                                        $stroke = match (true) {
                                            $score >= 80 => '#22c55e',
                                            $score >= 60 => '#eab308',
                                            $score >= 40 => '#f97316',
                                            default => '#ef4444',
                                        };
                                    @endphp
                                    <div class="text-center" style="flex-shrink:0;">
                                        <div class="position-relative" style="width:64px;height:64px;">
                                            <svg viewBox="0 0 56 56" style="width:64px;height:64px;transform:rotate(-90deg);">
                                                <circle cx="28" cy="28" r="{{ $radius }}" fill="none" stroke="#e5e7eb" stroke-width="6"></circle>
                                                <circle cx="28" cy="28" r="{{ $radius }}" fill="none" stroke="{{ $stroke }}" stroke-width="6"
                                                    stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"></circle>
                                            </svg>
                                            <div class="position-absolute d-flex flex-column align-items-center justify-content-center w-100 h-100" style="top:0;left:0;">
                                                <span class="f-14 font-weight-bold text-darkest-grey">{{ number_format($score) }}</span>
                                                <span class="f-10 text-lightest text-uppercase">Score</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach ([
                            ['label' => 'Total Data Used', 'value' => $formatBytes($totalDataBytes)],
                            ['label' => 'Upload Volume', 'value' => $formatBytes($totalUploadBytes)],
                            ['label' => 'Download Volume', 'value' => $formatBytes($totalDownloadBytes)],
                            ['label' => 'Peak Activity Hour', 'value' => $peakHour],
                        ] as $metric)
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="border-grey rounded p-3 h-100">
                                    <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                                    <div class="mt-3 f-21 f-w-500 text-darkest-grey">{{ $metric['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">AI Network Summary</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Conclusions first, bandwidth numbers second.</p>
                </div>
                <div class="card-body p-20">
                    <div class="bg-grey rounded p-20 mb-3">
                        <p class="f-14 text-dark-grey mb-0">“{{ $networkSummary }}”</p>
                    </div>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-secondary mr-2 mb-2">Upload share: {{ number_format($bandwidthComparison, 1) }}%</span>
                        <span class="badge badge-secondary mr-2 mb-2">Alert count: {{ number_format($alertCount) }}</span>
                        <span class="badge badge-secondary mb-2">Peak hour: {{ $peakHour }}</span>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Bandwidth Timeline</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Hover the bars to compare upload and download activity by hour.</p>
                </div>
                <div class="card-body p-20">
                    @if ($hasData)
                        @foreach ($timelineRows as $hour)
                            @php
                                $total = max(1, $hour['total']);
                                $uploadWidth = max(4, (int) round(($hour['uploaded'] / $total) * 100));
                                $downloadWidth = max(4, (int) round(($hour['downloaded'] / $total) * 100));
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                                <div class="f-12 f-w-500 text-dark-grey text-right mr-3" style="width:56px;">{{ $hour['label'] }}</div>
                                <div class="flex-grow-1 border-grey rounded overflow-hidden d-flex" style="height:36px;"
                                    title="{{ $hour['label'] }} | Upload {{ $formatBytes((int) $hour['uploaded']) }} | Download {{ $formatBytes((int) $hour['downloaded']) }} | Total {{ $formatBytes((int) $hour['total']) }}">
                                    <div class="d-flex align-items-center justify-content-center f-11 f-w-500 text-white bg-primary"
                                        style="width:{{ $uploadWidth }}%;opacity:{{ $hour['level'] === 'idle' ? 0.2 : 1 }};">Upload</div>
                                    <div class="d-flex align-items-center justify-content-center f-11 f-w-500 text-white bg-info"
                                        style="width:{{ $downloadWidth }}%;opacity:{{ $hour['level'] === 'idle' ? 0.2 : 1 }};">Download</div>
                                </div>
                                <div class="f-12 f-w-500 text-darkest-grey text-right ml-3" style="width:72px;">{{ $formatBytes((int) $hour['total']) }}</div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-center f-14 text-lightest py-4 mb-0">@lang('monitor::app.noNetworkData')</p>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Traffic Sources</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">The biggest bandwidth consumers, ranked for quick review.</p>
                        </div>
                        <div class="card-body p-20">
                            @forelse ($topSources as $source)
                                <div class="border-grey rounded p-3 mb-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="f-14 f-w-500 text-darkest-grey">{{ $source['name'] }}</div>
                                            <div class="f-12 text-lightest mt-1">{{ $source['category'] }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="f-14 f-w-500 text-darkest-grey">{{ $formatBytes((int) $source['bytes']) }}</div>
                                            <div class="f-12 text-lightest">{{ number_format(($source['bytes'] / $maxSourceBytes) * 100, 0) }}%</div>
                                        </div>
                                    </div>
                                    <div class="progress mt-3" style="height:8px;">
                                        <div class="progress-bar progress-bar-primary" style="width:{{ max(8, round(($source['bytes'] / $maxSourceBytes) * 100)) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No traffic sources available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Work Services Analysis</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Categories make the day easier to understand than raw MB.</p>
                        </div>
                        <div class="card-body p-20">
                            @foreach ($serviceBreakdown as $item)
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="f-14 f-w-500 text-darkest-grey">{{ $item['label'] }}</span>
                                        <span class="f-12 text-lightest">{{ $item['value'] }} · {{ number_format((float) $item['pct'], 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar progress-bar-primary" style="width:{{ $item['pct'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Activity Correlation</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">What the network was doing when apps and websites were active.</p>
                </div>
                <div class="card-body p-20">
                    @forelse ($correlations->take(6) as $item)
                        <div class="border-grey rounded p-3 mb-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="f-14 f-w-500 text-darkest-grey">{{ $item['hour_label'] }}</div>
                                    <div class="f-12 text-lightest mt-1">{{ $item['activity'] }}</div>
                                </div>
                                <span class="badge badge-primary">{{ $item['total'] }}</span>
                            </div>
                            <div class="d-flex flex-wrap mt-3">
                                @foreach ($item['apps'] as $app)
                                    <span class="badge badge-secondary mr-2 mb-2">{{ $app }}</span>
                                @endforeach
                                @foreach ($item['sites'] as $site)
                                    <span class="badge badge-primary mr-2 mb-2">{{ $site }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No network correlations available.</p>
                    @endforelse
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Anomalies & Alerts</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Highlights that may need manager review.</p>
                        </div>
                        <div class="card-body p-20">
                            @foreach ($anomalies as $anomaly)
                                @php
                                    $anomalyPanel = match ($anomaly['tone']) {
                                        'red' => 'border-danger bg-light-danger',
                                        'amber' => 'border-warning bg-light-warning',
                                        default => 'border-success bg-light-success',
                                    };
                                @endphp
                                <div class="rounded border p-3 mb-3 {{ $anomalyPanel }}">
                                    <div class="f-14 f-w-500 text-darkest-grey">{{ $anomaly['title'] }}</div>
                                    <div class="f-12 text-lightest mt-1">{{ $anomaly['detail'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Network Sessions</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Grouped periods that tell the story of the day.</p>
                        </div>
                        <div class="card-body p-20">
                            @forelse ($sessions->take(4) as $session)
                                @php
                                    $sessionBadge = match ($session['category']) {
                                        'Development' => 'badge-success',
                                        'Communication' => 'badge-warning',
                                        'AI Tools' => 'badge-primary',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <div class="border-grey rounded p-3 mb-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="f-14 f-w-500 text-darkest-grey">{{ $session['label'] }}</div>
                                            <div class="f-12 text-lightest mt-1">{{ $session['start_label'] }} - {{ $session['end_label'] }}</div>
                                        </div>
                                        <span class="badge badge-primary">{{ $session['bytes_label'] }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap mt-3">
                                        @foreach ($session['sources'] as $source)
                                            <span class="badge badge-secondary mr-2 mb-2">{{ $source }}</span>
                                        @endforeach
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge {{ $sessionBadge }}">{{ $session['category'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No sessions available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Detailed Network Logs</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Collapsed by default for managers who only need the raw evidence occasionally.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-network-log-toggle>
                            <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                            <span>Expand detailed logs</span>
                        </button>
                    </div>
                </div>
                <div class="d-none card-body p-20" data-network-log-panel>
                    @if ($hasData)
                        <div class="table-responsive">
                            <table class="table table-hover w-100">
                                <thead>
                                    <tr class="border-bottom-grey">
                                        <th class="f-11 text-lightest text-uppercase">@lang('app.time')</th>
                                        <th class="f-11 text-lightest text-uppercase">Uploaded</th>
                                        <th class="f-11 text-lightest text-uppercase">Downloaded</th>
                                        <th class="f-11 text-lightest text-uppercase">Total</th>
                                        <th class="f-11 text-lightest text-uppercase">Category</th>
                                        <th class="f-11 text-lightest text-uppercase">@lang('app.status')</th>
                                        <th class="f-11 text-lightest text-uppercase">@lang('app.details')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $log)
                                        @php
                                            $logCategory = collect($log['top_processes'] ?? [])->pluck('process')->filter()->first();
                                            $category = $classifySource((string) $logCategory);
                                            $statusFlags = collect();
                                            if (!empty($log['vpn_active'])) {
                                                $statusFlags->push('VPN active');
                                            }
                                            if (!empty($log['large_transfer_alert'])) {
                                                $statusFlags->push('Large transfer');
                                            }
                                            if (!empty($log['has_cloud_alert'])) {
                                                $statusFlags->push('Cloud upload');
                                            }
                                            $categoryBadge = match ($category) {
                                                'Development' => 'badge-success',
                                                'Communication' => 'badge-warning',
                                                'AI Tools' => 'badge-primary',
                                                default => 'badge-secondary',
                                            };
                                        @endphp
                                        <tr class="{{ !empty($log['has_cloud_alert']) ? 'bg-light-warning' : '' }}">
                                            <td class="text-nowrap f-w-500 text-darkest-grey">{{ $log['hour'] ?? '—' }}</td>
                                            <td class="text-dark-grey">{{ $formatBytes((int) ($log['uploaded_bytes'] ?? 0)) }}</td>
                                            <td class="text-dark-grey">{{ $formatBytes((int) ($log['downloaded_bytes'] ?? 0)) }}</td>
                                            <td class="f-w-500 text-darkest-grey">{{ $formatBytes((int) ($log['total_bytes'] ?? 0)) }}</td>
                                            <td><span class="badge {{ $categoryBadge }}">{{ $category }}</span></td>
                                            <td>
                                                <div class="d-flex flex-wrap">
                                                    @forelse ($statusFlags as $flag)
                                                        <span class="badge badge-secondary mr-1 mb-1">{{ $flag }}</span>
                                                    @empty
                                                        <span class="badge badge-success">Normal</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="f-12 text-dark-grey">
                                                @if (!empty($log['cloud_uploads']))
                                                    <div class="f-w-500 text-darkest-grey">{{ implode(', ', $log['cloud_uploads']) }}</div>
                                                @endif
                                                @if ($log['top_processes']->isNotEmpty())
                                                    <div class="mt-1">{{ $log['top_processes']->pluck('process')->filter()->implode(', ') }}</div>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center f-14 text-lightest py-4 mb-0">@lang('monitor::app.noNetworkData')</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Network Health Widget</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Sticky manager summary for quick context.</p>
                </div>
                <div class="card-body p-20">
                    <div class="bg-additional-grey rounded p-3 mb-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="f-11 text-lightest text-uppercase">Network Score</div>
                                <div class="mt-2 f-21 f-w-500 text-darkest-grey">{{ number_format($networkScore) }}/100</div>
                                <div class="f-14 text-dark-grey mt-1">{{ $networkScoreLabel }}</div>
                            </div>
                            <span class="badge {{ $networkBadgeClass }}">{{ $networkScoreLabel }}</span>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ([
                            ['label' => 'Current Network Activity', 'value' => $currentNetworkActivity],
                            ['label' => 'Peak Hour', 'value' => $peakHour],
                            ['label' => 'Total Usage', 'value' => $formatBytes($totalDataBytes)],
                            ['label' => 'Alert Count', 'value' => number_format($alertCount)],
                        ] as $widgetMetric)
                            <div class="col-sm-6 mb-3">
                                <div class="border-grey rounded bg-white p-3 h-100">
                                    <div class="f-11 text-lightest text-uppercase">{{ $widgetMetric['label'] }}</div>
                                    <div class="mt-1 f-w-500 text-darkest-grey">{{ $widgetMetric['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 mb-3">
                            <div class="border-grey rounded bg-white p-3">
                                <div class="f-11 text-lightest text-uppercase">Most Active Service</div>
                                <div class="mt-1 text-truncate f-w-500 text-darkest-grey">{{ $topSources->first()['name'] ?? 'No active service' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border-grey rounded bg-white p-3">
                        <div class="f-11 text-lightest text-uppercase">Quick Stats</div>
                        <div class="mt-3">
                            @foreach ([
                                ['label' => 'Upload volume', 'value' => $formatBytes($totalUploadBytes)],
                                ['label' => 'Download volume', 'value' => $formatBytes($totalDownloadBytes)],
                                ['label' => 'Peak hour total', 'value' => $formatBytes($peakHourBytes)],
                                ['label' => 'Alert rows', 'value' => number_format($alertCount)],
                            ] as $metric)
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="f-12 text-lightest">{{ $metric['label'] }}</span>
                                    <span class="f-w-500 text-darkest-grey">{{ $metric['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            $('body').off('click.monitorNetworkToggle').on('click.monitorNetworkToggle', '[data-network-log-toggle]', function () {
                const $panel = $('[data-network-log-panel]').first();
                const expanded = $panel.hasClass('d-none');
                $panel.toggleClass('d-none', !expanded);
                $(this).find('span').text(expanded ? 'Collapse detailed logs' : 'Expand detailed logs');
                $(this).find('i').toggleClass('fa-plus', !expanded).toggleClass('fa-minus', expanded);
            });
        });
    </script>
@endpush
