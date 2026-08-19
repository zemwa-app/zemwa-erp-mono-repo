@php use Illuminate\Support\Str; $rows = collect($timelineHours ?? []); $hasData = $rows->isNotEmpty(); $classifyWorkBucket = function (string $value): string { $haystack = strtolower($value); if (str_contains($haystack, 'vscode') || str_contains($haystack, 'visual studio') || str_contains($haystack, 'cursor') || str_contains($haystack, 'phpstorm') || str_contains($haystack, 'intellij') || str_contains($haystack, 'webstorm') || str_contains($haystack, 'pycharm') || str_contains($haystack, 'terminal') || str_contains($haystack, 'git') || str_contains($haystack, 'code')) { return 'Development'; } if (str_contains($haystack, 'docs') || str_contains($haystack, 'documentation') || str_contains($haystack, 'confluence') || str_contains($haystack, 'notion') || str_contains($haystack, 'readme') || str_contains($haystack, 'guide') || str_contains($haystack, 'manual')) { return 'Documentation'; } if (str_contains($haystack, 'research') || str_contains($haystack, 'google') || str_contains($haystack, 'search') || str_contains($haystack, 'stackoverflow') || str_contains($haystack, 'wikipedia') || str_contains($haystack, 'browser') || str_contains($haystack, 'chrome') || str_contains($haystack, 'firefox') || str_contains($haystack, 'edge') || str_contains($haystack, 'safari')) { return 'Research'; } if (str_contains($haystack, 'slack') || str_contains($haystack, 'teams') || str_contains($haystack, 'outlook') || str_contains($haystack, 'mail') || str_contains($haystack, 'gmail') || str_contains($haystack, 'whatsapp') || str_contains($haystack, 'discord') || str_contains($haystack, 'zoom') || str_contains($haystack, 'meet')) { return 'Communication'; } if (str_contains($haystack, 'calendar') || str_contains($haystack, 'meetings') || str_contains($haystack, 'zoom meeting') || str_contains($haystack, 'teams meeting')) { return 'Meetings'; } return 'Other'; }; $topApps = $rows ->flatMap(fn ($row) => collect($row['segments'] ?? [])->where('type', 'app')->pluck('app_name')) ->filter() ->countBy() ->sortDesc(); $activeSeconds = (int) $rows->sum('active_seconds'); $idleSeconds = (int) $rows->sum('idle_seconds'); $productiveSeconds = (int) $rows->sum('productive_seconds'); $neutralSeconds = (int) $rows->sum('neutral_seconds'); $unproductiveSeconds = (int) $rows->sum('unproductive_seconds'); $contextSwitches = (int) $rows->sum('context_switches'); $hourCount = max(1, $rows->where('total_seconds', '>', 0)->count()); $avgHourlyScore = round((float) ($rows->where('total_seconds', '>', 0)->avg('productivity_score') ?? 0), 1); $focusScore = $activeSeconds > 0 ? round(($productiveSeconds / max(1, $activeSeconds)) * 100, 1) : 0; $idlePct = ($activeSeconds + $idleSeconds) > 0 ? round(($idleSeconds / max(1, $activeSeconds + $idleSeconds)) * 100, 1) : 0; $contextSwitchingScore = (int) max(0, min(100, 100 - round(($contextSwitches / max(1, $hourCount)) * 7))); $idleEventCount = (int) $rows->where('idle_seconds', '>', 0)->count(); $interruptionScore = (int) max(0, min(100, 100 - round($idlePct + ($idleEventCount * 4)))); $consistencyScore = (int) round((float) ($rows->where('total_seconds', '>', 0)->avg('productivity_score') ?? 0)); $workdayScore = (int) max(0, min(100, round(($focusScore * 0.35) + ($consistencyScore * 0.35) + ($contextSwitchingScore * 0.15) + ($interruptionScore * 0.15)))); $workdayScoreLabel = match (true) { $workdayScore >= 90 => 'Excellent', $workdayScore >= 75 => 'Good', $workdayScore >= 60 => 'Needs Attention', default => 'Critical', }; $workdayScoreTone = match (true) { $workdayScore >= 90 => 'green', $workdayScore >= 75 => 'emerald', $workdayScore >= 60 => 'amber', default => 'red', }; $focusSessions = []; $currentSession = null; foreach ($rows as $row) { $hour = (int) ($row['hour'] ?? 0); $rowScore = (int) ($row['productivity_score'] ?? 0); $rowActive = (int) ($row['active_seconds'] ?? 0); $isFocus = $rowScore >= 65 && $rowActive > 0; if ($isFocus) { $primaryApps = collect($row['segments'] ?? [])->where('type', 'app')->pluck('app_name')->filter()->values(); if (!$currentSession) { $currentSession = [ 'start_hour' => $hour, 'end_hour' => $hour, 'seconds' => $rowActive, 'app_names' => $primaryApps, ]; } elseif ($hour === ($currentSession['end_hour'] + 1)) { $currentSession['end_hour'] = $hour; $currentSession['seconds'] += $rowActive; $currentSession['app_names'] = $currentSession['app_names']->merge($primaryApps)->unique()->values(); } else { $focusSessions[] = $currentSession; $currentSession = [ 'start_hour' => $hour, 'end_hour' => $hour, 'seconds' => $rowActive, 'app_names' => $primaryApps, ]; } } elseif ($currentSession) { $focusSessions[] = $currentSession; $currentSession = null; } } if ($currentSession) { $focusSessions[] = $currentSession; } $focusSessions = collect($focusSessions)->map(function (array $session) use ($rows, $classifyWorkBucket) { $startRow = $rows->firstWhere('hour', $session['start_hour']) ?? []; $endRow = $rows->firstWhere('hour', $session['end_hour']) ?? []; $startLabel = $startRow['label'] ?? ''; $endLabel = $endRow['label'] ?? ''; $apps = collect($session['app_names'] ?? [])->filter()->values(); $theme = $classifyWorkBucket($apps->first() ?? ''); return [ 'start_label' => $startLabel, 'end_label' => $endLabel, 'duration_label' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) $session['seconds']), 'app_line' => $apps->isNotEmpty() ? $apps->implode(', ') : 'Focused work', 'theme' => $theme, 'seconds' => (int) $session['seconds'], ]; })->sortByDesc('seconds')->values(); $longestFocusBlock = $focusSessions->first(); $interruptionGapMinutes = $idleEventCount > 0 ? round(($idleSeconds / 60) / max(1, $idleEventCount), 1) : 0; $slackInterruptions = (int) $rows->filter(function ($row) { $haystack = strtolower((string) ($row['primary_app'] ?? '')); return str_contains($haystack, 'slack') || str_contains($haystack, 'teams') || str_contains($haystack, 'mail') || str_contains($haystack, 'outlook') || str_contains($haystack, 'discord'); })->count(); $switchHeavyHours = (int) $rows->filter(fn ($row) => (int) ($row['context_switches'] ?? 0) >= 3)->count(); $productivityDrop = 0; $activeRows = $rows->where('total_seconds', '>', 0)->values(); if ($activeRows->count() >= 2) { $firstHalf = $activeRows->slice(0, (int) ceil($activeRows->count() / 2)); $secondHalf = $activeRows->slice((int) floor($activeRows->count() / 2)); $productivityDrop = max(0, round((float) (($firstHalf->avg('productivity_score') ?? 0) - ($secondHalf->avg('productivity_score') ?? 0)), 1)); } $workBuckets = [ 'Development' => 0, 'Research' => 0, 'Documentation' => 0, 'Communication' => 0, 'Meetings' => 0, 'Other' => 0, ]; foreach ($rows as $row) { foreach ($row['segments'] ?? [] as $segment) { if (($segment['type'] ?? '') !== 'app') { continue; } $bucket = $classifyWorkBucket((string) ($segment['app_name'] ?? '')); $workBuckets[$bucket] = ($workBuckets[$bucket] ?? 0) + (int) ($segment['seconds'] ?? 0); } } $workBucketTotal = max(1, array_sum($workBuckets)); $workBuckets = collect($workBuckets)->map(function (int $seconds, string $label) use ($workBucketTotal) { return [ 'label' => $label, 'seconds' => $seconds, 'label_seconds' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($seconds), 'pct' => round(($seconds / $workBucketTotal) * 100, 1), ]; })->filter(fn ($item) => $item['seconds'] > 0)->sortByDesc('seconds')->values(); $hourLabels = collect(range(7, 23))->map(function (int $hour) use ($rows) { $row = $rows->firstWhere('hour', $hour); $score = (int) ($row['productivity_score'] ?? 0); $idle = (int) ($row['idle_seconds'] ?? 0); $active = (int) ($row['active_seconds'] ?? 0); $level = match (true) { $active === 0 && $idle > 0 => 'idle', $score >= 80 => 'high', $score >= 55 => 'medium', $score > 0 => 'low', default => 'idle', }; return [ 'hour' => $hour, 'label' => \Carbon\Carbon::createFromTime($hour, 0, 0, company()->timezone)->format('g A'), 'score' => $score, 'level' => $level, 'has_data' => (bool) $row, ]; }); $rowAverageScore = $rows->where('total_seconds', '>', 0)->avg('productivity_score') ?? 0; $morningRows = $rows->filter(fn ($row) => (int) ($row['hour'] ?? 0) < 12 && (int) ($row['total_seconds'] ?? 0) > 0); $afternoonRows = $rows->filter(fn ($row) => (int) ($row['hour'] ?? 0) >= 12 && (int) ($row['total_seconds'] ?? 0) > 0); $trendDelta = round((float) (($afternoonRows->avg('productivity_score') ?? 0) - ($morningRows->avg('productivity_score') ?? 0)), 1); $trendLabel = ($trendDelta >= 0 ? '+' : '') . number_format($trendDelta, 1) . '% vs morning'; $timelineEvents = $rows->map(function ($row) { return [ 'time' => $row['label'] ?? '—', 'application' => $row['primary_app'] ?? 'Idle', 'category' => $row['primary_category'] ?? ($row['idle_seconds'] > 0 ? 'idle' : 'neutral'), 'productivity' => (int) ($row['productivity_score'] ?? 0), 'duration_label' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($row['active_seconds'] ?? 0)), 'activity' => (($row['context_switches'] ?? 0) > 0 ? 'Switch-heavy' : ((int) ($row['active_seconds'] ?? 0) > 0 ? 'Focused' : 'Idle')), 'details' => sprintf( '%s keystrokes · %s clicks · %s scrolls', $row['stats']['keystrokes'] ?? '0', $row['stats']['mouse_clicks'] ?? '0', $row['stats']['scroll_events'] ?? '0' ), 'search_haystack' => strtolower(implode(' ', array_filter([ $row['label'] ?? '', $row['primary_app'] ?? '', $row['primary_category'] ?? '', $row['stats']['keystrokes'] ?? '', $row['stats']['mouse_clicks'] ?? '', $row['stats']['scroll_events'] ?? '', ]))), ]; }); $currentRow = $rows->filter(fn ($row) => (int) ($row['total_seconds'] ?? 0) > 0)->last() ?? $rows->last(); $currentStatus = ($currentRow['idle_seconds'] ?? 0) > ($currentRow['active_seconds'] ?? 0) ? 'Idle' : 'Active'; $currentApp = $currentRow['primary_app'] ?? 'No active app'; $currentSession = $currentRow ? ($currentRow['label'] ?? '—') . ' · ' . \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($currentRow['active_seconds'] ?? 0)) : '—'; $focusScoreLabel = match (true) { $focusScore >= 90 => 'Excellent', $focusScore >= 75 => 'Good', $focusScore >= 60 => 'Needs Attention', default => 'Critical', }; $focusScoreTone = match (true) { $focusScore >= 90 => 'green', $focusScore >= 75 => 'emerald', $focusScore >= 60 => 'amber', default => 'red', }; $positiveSignals = collect(); if ($longestFocusBlock) { $positiveSignals->push(['label' => 'Long focus blocks', 'detail' => $longestFocusBlock['duration_label'] . ' uninterrupted', 'tone' => 'green']); } if ($idleSeconds < 20 * 60) { $positiveSignals->push(['label' => 'Low idle time', 'detail' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($idleSeconds) . ' idle', 'tone' => 'green']); } if ($focusScore >= 70) { $positiveSignals->push(['label' => 'Strong coding sessions', 'detail' => 'Productive time stayed high', 'tone' => 'green']); } if ($workdayScore >= 75) { $positiveSignals->push(['label' => 'Consistent work rhythm', 'detail' => 'The day stayed relatively steady', 'tone' => 'green']); } $attentionSignals = collect(); if ($contextSwitches >= 12) { $attentionSignals->push(['label' => 'Frequent context switching', 'detail' => $contextSwitches . ' switches recorded', 'tone' => 'amber']); } if ($idleEventCount >= 3) { $attentionSignals->push(['label' => 'High interruption count', 'detail' => $idleEventCount . ' idle events', 'tone' => 'amber']); } if ($idleSeconds >= 30 * 60) { $attentionSignals->push(['label' => 'Long idle period', 'detail' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($idleSeconds) . ' idle', 'tone' => 'amber']); } if ($productivityDrop >= 10) { $attentionSignals->push(['label' => 'Productivity drop', 'detail' => 'Afternoon trended lower by ' . number_format($productivityDrop, 1) . '%', 'tone' => 'amber']); } $intensityClass = function (?int $score, int $idleSeconds = 0): string {
    if ($idleSeconds > 0 && (!$score || $score < 35)) {
        return 'bg-secondary';
    }

    return match (true) {
        ($score ?? 0) >= 80 => 'bg-success',
        ($score ?? 0) >= 60 => 'bg-warning',
        ($score ?? 0) >= 40 => 'bg-warning',
        ($score ?? 0) > 0 => 'bg-danger',
        default => 'bg-secondary',
    };
};
$workdaySummary = $hasData ? ( $workdayScore >= 85 ? 'Strong focus throughout the day with multiple productive blocks. Most activity happened in ' . ($topApps->take(3)->keys()->implode(', ') ?: 'the core work apps') . '. Idle time stayed low and workflow remained healthy.' : ($contextSwitches >= 12 ? 'Frequent application switching between mid-day hours reduced focus efficiency. Productive time was present, but interruptions and switching were more visible than usual.' : 'The workday showed a balanced mix of productive work and lighter activity. No severe interruptions stood out, but the flow was more mixed than a peak-focus day.') ) : 'No timeline data available for this date.';
@endphp

<div class="p-20">
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Workday Summary</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">A manager's first read on how the day unfolded.</p>
                        </div>
                        <span class="badge {{ $workdayScoreTone === 'green' || $workdayScoreTone === 'emerald' ? 'badge-success' : ($workdayScoreTone === 'amber' ? 'badge-warning' : ($workdayScoreTone === 'red' ? 'badge-danger' : 'badge-secondary')) }}">
                            {{ $workdayScoreLabel }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-20">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="border-grey rounded p-3 h-100">
                                <div class="f-11 text-lightest text-uppercase">Workday Score</div>
                                <div class="mt-3 d-flex align-items-end justify-content-between">
                                    <div class="f-21 font-weight-bold text-darkest-grey">{{ number_format($workdayScore) }}/100</div>
                                    @php
                                        $score = max(0, min(100, $workdayScore));
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
                            ['label' => 'Active Time', 'value' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($activeSeconds)],
                            ['label' => 'Focus Sessions', 'value' => number_format($focusSessions->count())],
                            ['label' => 'Longest Focus Block', 'value' => $longestFocusBlock['duration_label'] ?? '0m'],
                            ['label' => 'Idle Time', 'value' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($idleSeconds)],
                            ['label' => 'Context Switches', 'value' => number_format($contextSwitches)],
                        ] as $metric)
                            <div class="col-lg-4 col-md-6 mb-3">
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
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">AI Workday Summary</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Conclusions first, data second.</p>
                </div>
                <div class="card-body p-20">
                    <div class="bg-grey rounded p-20 mb-3">
                        <p class="f-14 text-dark-grey mb-0">"{{ $workdaySummary }}"</p>
                    </div>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-secondary mr-2 mb-2">Focus score: {{ number_format($focusScore, 1) }}%</span>
                        <span class="badge badge-secondary mr-2 mb-2">Context switching score: {{ number_format($contextSwitchingScore) }}/100</span>
                        <span class="badge badge-secondary mr-2 mb-2">Interruption score: {{ number_format($interruptionScore) }}/100</span>
                        <span class="badge badge-secondary mb-2">Consistency score: {{ number_format($consistencyScore) }}/100</span>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Workday Playback</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">The hourly bars still exist, now with playback context and productivity detail.</p>
                        </div>
                        <div class="d-flex flex-wrap monitor-playback-legend">
                            <span class="badge badge-success mr-2 mb-1"><span class="d-inline-block rounded-circle bg-success mr-1" style="width:8px;height:8px;"></span> High productivity</span>
                            <span class="badge badge-warning mr-2 mb-1"><span class="d-inline-block rounded-circle bg-warning mr-1" style="width:8px;height:8px;"></span> Medium productivity</span>
                            <span class="badge badge-danger mr-2 mb-1"><span class="d-inline-block rounded-circle bg-danger mr-1" style="width:8px;height:8px;"></span> Low productivity</span>
                            <span class="badge badge-secondary mb-1"><span class="d-inline-block rounded-circle bg-secondary mr-1" style="width:8px;height:8px;"></span> Idle</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-20">
                    @forelse ($rows as $row)
                        @php
                            $segments = collect($row['segments'] ?? []);
                            $hasActivity = (int) ($row['total_seconds'] ?? 0) > 0 || $segments->isNotEmpty();
                            $rowScore = (int) ($row['productivity_score'] ?? 0);
                            $rowPrimaryApp = $row['primary_app'] ?? 'Idle';
                            $rowActivity = ($row['idle_seconds'] ?? 0) > ($row['active_seconds'] ?? 0) ? 'Idle Session' : (($row['context_switches'] ?? 0) > 2 ? 'Switch-heavy' : 'Focus Session');
                            $rowCategory = $row['primary_category'] ?? (($row['idle_seconds'] ?? 0) > 0 ? 'idle' : 'neutral');
                            $rowToneClass = match (true) {
                                !$hasActivity => 'progress-bar-secondary',
                                ($row['idle_seconds'] ?? 0) > ($row['active_seconds'] ?? 0) => 'progress-bar-secondary',
                                $rowScore >= 80 => 'progress-bar-success',
                                $rowScore >= 55 => 'progress-bar-warning',
                                $rowScore > 0 => 'progress-bar-danger',
                                default => 'progress-bar-secondary',
                            };
                        @endphp
                        <div class="monitor-playback-hour {{ $hasActivity ? '' : 'monitor-playback-hour--empty' }}">
                            <div class="d-flex align-items-center">
                                <span class="monitor-playback-hour__time">{{ $row['label'] }}</span>
                                <div class="monitor-playback-track flex-grow-1">
                                    @if ($hasActivity && $segments->isNotEmpty())
                                        @foreach ($segments as $segment)
                                            @if (($segment['type'] ?? '') === 'idle')
                                                <div class="monitor-timeline-idle"
                                                    style="width:{{ $segment['width_pct'] ?? 100 }}%;"
                                                    title="Idle | {{ $row['label'] }} | {{ \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($segment['seconds'] ?? 0)) }}">
                                                    @lang('monitor::app.idle')
                                                </div>
                                            @else
                                                @php
                                                    $segmentBarClass = match ($segment['category'] ?? '') {
                                                        'productive' => 'monitor-playback-segment--high',
                                                        'unproductive' => 'monitor-playback-segment--low',
                                                        default => 'monitor-playback-segment--medium',
                                                    };
                                                @endphp
                                                <div class="monitor-playback-segment {{ $segmentBarClass }}"
                                                    style="width:{{ $segment['width_pct'] ?? 0 }}%;"
                                                    title="{{ $segment['app_name'] ?? 'Unknown' }} | {{ $segment['started_at'] ?? $row['label'] }} - {{ $segment['ended_at'] ?? $row['label'] }} | {{ \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($segment['seconds'] ?? 0)) }} | {{ \Modules\Monitor\Services\MonitorEmployeeDetailService::categoryLabel($segment['category'] ?? null) }}">
                                                    <span class="text-truncate">{{ $segment['app_name'] ?? 'Unknown' }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="monitor-playback-track__empty">@lang('monitor::app.noActivity')</div>
                                    @endif
                                </div>
                                <div class="monitor-playback-hour__score">
                                    <div class="f-12 f-w-500 text-darkest-grey text-right">{{ number_format($rowScore) }}/100</div>
                                    <div class="progress mt-1" style="height:6px;">
                                        <div class="progress-bar {{ $rowToneClass }}" style="width:{{ max(0, min(100, $rowScore)) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @if ($hasActivity)
                                <div class="monitor-playback-hour__meta">
                                    <div class="monitor-playback-hour__stats">
                                        <span><i class="fa fa-keyboard mr-1" aria-hidden="true"></i>{{ $row['stats']['keystrokes'] ?? '0' }} keystrokes</span>
                                        <span><i class="fa fa-mouse-pointer mr-1" aria-hidden="true"></i>{{ $row['stats']['mouse_clicks'] ?? '0' }} clicks</span>
                                        <span><i class="fa fa-arrows-alt-v mr-1" aria-hidden="true"></i>{{ $row['stats']['scroll_events'] ?? '0' }} scrolls</span>
                                        <span><i class="fa fa-random mr-1" aria-hidden="true"></i>{{ number_format((int) ($row['context_switches'] ?? 0)) }} switches</span>
                                        <span><i class="fa fa-stopwatch mr-1" aria-hidden="true"></i>{{ \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($row['active_seconds'] ?? 0)) }} active</span>
                                    </div>
                                    <div class="monitor-playback-hour__tags d-flex flex-wrap mt-2">
                                        <span class="badge badge-secondary mr-2 mb-1">{{ $rowActivity }}</span>
                                        <span class="badge badge-secondary mr-2 mb-1">{{ $rowPrimaryApp }}</span>
                                        <span class="badge badge-secondary mb-1">{{ $rowCategory === 'idle' ? 'Idle' : \Modules\Monitor\Services\MonitorEmployeeDetailService::categoryLabel($rowCategory) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center f-14 text-lightest py-4 mb-0">@lang('monitor::app.noActivity')</p>
                    @endforelse
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Focus Sessions</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Major uninterrupted work blocks from the day.</p>
                        </div>
                        <div class="card-body p-20">
                            @forelse ($focusSessions->take(3) as $session)
                                @php
                                    $sessionBadge = match ($session['theme']) {
                                        'Development' => 'badge-success',
                                        'Documentation' => 'badge-primary',
                                        'Communication' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <div class="border-grey rounded p-3 mb-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="f-14 f-w-500 text-darkest-grey">Focus Session</div>
                                            <div class="f-12 text-lightest mt-1">{{ $session['start_label'] }} - {{ $session['end_label'] }}</div>
                                        </div>
                                        <span class="badge badge-primary">{{ $session['duration_label'] }}</span>
                                    </div>
                                    <div class="f-14 text-dark-grey mt-3">{{ $session['app_line'] }}</div>
                                    <div class="mt-2"><span class="badge {{ $sessionBadge }}">{{ $session['theme'] }}</span></div>
                                </div>
                            @empty
                                <p class="text-center f-14 text-lightest py-4 mb-0">No focus sessions detected.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Interruptions Analysis</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Where the workflow got interrupted or fragmented.</p>
                        </div>
                        <div class="card-body p-20">
                            <div class="row">
                                @foreach ([
                                    ['label' => 'Slack interruptions', 'value' => number_format($slackInterruptions)],
                                    ['label' => 'Application switches', 'value' => number_format($contextSwitches)],
                                    ['label' => 'Idle events', 'value' => number_format($idleEventCount)],
                                    ['label' => 'Average interruption gap', 'value' => $interruptionGapMinutes . ' min'],
                                ] as $item)
                                    <div class="col-sm-6 mb-3">
                                        <div class="border-grey rounded p-3 h-100">
                                            <div class="f-11 text-lightest text-uppercase">{{ $item['label'] }}</div>
                                            <div class="mt-3 f-21 f-w-500 text-darkest-grey">{{ $item['value'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Productivity Heatmap</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">A quick visual of the day from 7 AM to 11 PM.</p>
                </div>
                <div class="card-body p-20">
                    <div class="monitor-productivity-heatmap">
                        @foreach ($hourLabels as $hour)
                            @php
                                $heatmapBarClass = match ($hour['level']) {
                                    'high' => 'monitor-productivity-heatmap__bar--high',
                                    'medium' => 'monitor-productivity-heatmap__bar--medium',
                                    'low' => 'monitor-productivity-heatmap__bar--low',
                                    default => 'monitor-productivity-heatmap__bar--idle',
                                };
                                $barOpacity = $hour['has_data'] ? (max(30, min(100, $hour['score'] ?: 25)) / 100) : 0.35;
                            @endphp
                            <div class="monitor-productivity-heatmap__item border-grey rounded p-3 text-center">
                                <div class="f-11 text-lightest text-uppercase monitor-productivity-heatmap__label">{{ $hour['label'] }}</div>
                                <div class="monitor-productivity-heatmap__bar mt-2 rounded {{ $heatmapBarClass }}" style="opacity:{{ $barOpacity }};"></div>
                                <div class="mt-2 f-12 f-w-500 text-darkest-grey">{{ $hour['has_data'] ? ($hour['score'] . '/100') : '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Activity Breakdown</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Where time went across work buckets instead of raw apps alone.</p>
                </div>
                <div class="card-body p-20">
                    @forelse ($workBuckets as $bucket)
                        @php
                            $bucketBarClass = match ($bucket['label']) {
                                'Development' => 'progress-bar-success',
                                'Research' => 'progress-bar-primary',
                                'Documentation' => 'progress-bar-info',
                                'Communication' => 'progress-bar-warning',
                                'Meetings' => 'progress-bar-warning',
                                default => 'progress-bar-primary',
                            };
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="f-14 f-w-500 text-darkest-grey">{{ $bucket['label'] }}</span>
                                <span class="f-12 text-lightest">{{ $bucket['label_seconds'] }} · {{ number_format((float) $bucket['pct'], 1) }}%</span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar {{ $bucketBarClass }}" style="width:{{ $bucket['pct'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No activity breakdown available.</p>
                    @endforelse
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Timeline Insights</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Signals that help a manager decide whether the day was healthy or noisy.</p>
                        </div>
                        <div class="d-flex flex-wrap">
                            <span class="badge badge-success mr-2 mb-1">
                                {{ $positiveSignals->count() }} {{ $positiveSignals->count() === 1 ? 'signal' : 'signals' }}
                            </span>
                            <span class="badge {{ $attentionSignals->count() > 0 ? 'badge-warning' : 'badge-secondary' }} mb-1">
                                {{ $attentionSignals->count() }} {{ $attentionSignals->count() === 1 ? 'item needs' : 'items need' }} review
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        @include('monitor::employees.ajax.partials.insight-card', [
                            'title' => 'Positive Signals',
                            'subtitle' => 'What went well during the workday',
                            'items' => $positiveSignals->all(),
                            'tone' => 'green',
                            'emptyText' => 'No positive signals detected.',
                            'borderRight' => true,
                        ])

                        @include('monitor::employees.ajax.partials.insight-card', [
                            'title' => 'Attention Signals',
                            'subtitle' => 'What may need a quick manager review',
                            'items' => $attentionSignals->all(),
                            'tone' => 'amber',
                            'emptyText' => 'No attention signals detected.',
                        ])
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Detailed Timeline Events</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Collapsed by default. Managers usually investigate this only when needed.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-timeline-toggle>
                            <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                            <span>Expand detailed events</span>
                        </button>
                    </div>
                </div>
                <div class="d-none card-body p-20" data-timeline-panel>
                    <div class="monitor-search-panel table-responsive">
                        @include('monitor::partials.table-search', [
                            'id' => 'monitor-timeline-search',
                            'placeholder' => __('app.search'),
                        ])
                        <table class="table table-hover w-100 monitor-searchable-table">
                            <thead>
                                <tr class="border-bottom-grey">
                                    <th class="f-11 text-lightest text-uppercase">Time</th>
                                    <th class="f-11 text-lightest text-uppercase">Application</th>
                                    <th class="f-11 text-lightest text-uppercase">Category</th>
                                    <th class="f-11 text-lightest text-uppercase">Productivity</th>
                                    <th class="f-11 text-lightest text-uppercase">Duration</th>
                                    <th class="f-11 text-lightest text-uppercase">Activity</th>
                                    <th class="f-11 text-lightest text-uppercase">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($timelineEvents as $event)
                                    @php
                                        $eventScore = (int) ($event['productivity'] ?? 0);
                                        $eventTone = match (true) {
                                            $eventScore >= 80 => 'badge-success',
                                            $eventScore >= 60 => 'badge-warning',
                                            $eventScore > 0 => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                        $categoryBadge = match (true) {
                                            $event['category'] === 'idle' => 'badge-secondary',
                                            $event['category'] === 'Communication' => 'badge-warning',
                                            $event['category'] === 'Development' => 'badge-success',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <tr class="monitor-search-row" data-search="{{ $event['search_haystack'] }}">
                                        <td class="text-nowrap f-w-500 text-darkest-grey">{{ $event['time'] }}</td>
                                        <td class="text-dark-grey">{{ $event['application'] }}</td>
                                        <td>
                                            <span class="badge {{ $categoryBadge }}">
                                                {{ $event['category'] === 'idle' ? 'Idle' : $event['category'] }}
                                            </span>
                                        </td>
                                        <td><span class="badge {{ $eventTone }}">{{ number_format($eventScore) }}/100</span></td>
                                        <td class="text-nowrap text-dark-grey">{{ $event['duration_label'] }}</td>
                                        <td class="text-dark-grey">{{ $event['activity'] }}</td>
                                        <td>{{ $event['details'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center f-14 text-lightest py-4">@lang('monitor::app.noActivity')</td>
                                    </tr>
                                @endforelse
                                @if ($timelineEvents->count() > 0)
                                    <tr class="monitor-search-empty d-none">
                                        <td colspan="7" class="text-center f-14 text-lightest py-4">@lang('messages.noRecordFound')</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Workday Health Widget</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Sticky summary for quick monitoring.</p>
                </div>
                <div class="card-body p-20">
                    <div class="bg-additional-grey rounded p-3 mb-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="f-11 text-lightest text-uppercase">Current Status</div>
                                <div class="mt-2 f-18 f-w-500 text-darkest-grey">{{ $currentStatus }}</div>
                            </div>
                            <span class="badge {{ $currentStatus === 'Idle' ? 'badge-secondary' : 'badge-success' }}">{{ $currentStatus }}</span>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ([
                            ['label' => 'Current App', 'value' => $currentApp],
                            ['label' => 'Current Session', 'value' => $currentSession],
                            ['label' => 'Focus Score', 'value' => number_format($focusScore, 1) . '%'],
                            ['label' => 'Workday Score', 'value' => number_format($workdayScore) . '/100'],
                            ['label' => 'Longest Focus Block', 'value' => $longestFocusBlock['duration_label'] ?? '0m'],
                            ['label' => 'Productivity Trend', 'value' => $trendLabel],
                        ] as $widgetMetric)
                            <div class="col-sm-6 mb-3">
                                <div class="border-grey rounded bg-white p-3 h-100">
                                    <div class="f-11 text-lightest text-uppercase">{{ $widgetMetric['label'] }}</div>
                                    <div class="mt-1 f-w-500 text-darkest-grey">{{ $widgetMetric['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-grey rounded bg-white p-3 mb-3">
                        <div class="f-11 text-lightest text-uppercase">Derived Metrics</div>
                        <div class="mt-3">
                            @foreach ([
                                ['label' => 'Focus Score', 'value' => number_format($focusScore, 1) . '%'],
                                ['label' => 'Workday Health Score', 'value' => number_format($workdayScore) . '/100'],
                                ['label' => 'Context Switching Score', 'value' => number_format($contextSwitchingScore) . '/100'],
                                ['label' => 'Interruption Score', 'value' => number_format($interruptionScore) . '/100'],
                                ['label' => 'Consistency Score', 'value' => number_format($consistencyScore) . '/100'],
                            ] as $metric)
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="f-12 text-lightest">{{ $metric['label'] }}</span>
                                    <span class="f-w-500 text-darkest-grey">{{ $metric['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="border-grey rounded bg-white p-3">
                        <div class="f-11 text-lightest text-uppercase">Productivity Trend</div>
                        <div class="d-flex align-items-end mt-3" style="height:80px;">
                            @foreach ($rows->take(12) as $row)
                                <div class="flex-grow-1 monitor-trend-bar {{ $intensityClass((int) ($row['productivity_score'] ?? 0), (int) ($row['idle_seconds'] ?? 0)) }} mr-1"
                                    style="height:{{ max(16, (int) ($row['productivity_score'] ?? 0)) / 1.3 }}px;"
                                    title="{{ $row['label'] }} · {{ (int) ($row['productivity_score'] ?? 0) }}/100"></div>
                            @endforeach
                        </div>
                        <div class="f-12 text-lightest mt-2">Morning vs afternoon trend: {{ $trendLabel }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            $('body').off('click.monitorTimelineToggle').on('click.monitorTimelineToggle', '[data-timeline-toggle]', function () {
                const $panel = $('[data-timeline-panel]').first();
                const expanded = $panel.hasClass('d-none');

                $panel.toggleClass('d-none', !expanded);
                $(this).find('span').text(expanded ? 'Collapse detailed events' : 'Expand detailed events');
                $(this).find('i').toggleClass('fa-plus', !expanded).toggleClass('fa-minus', expanded);
            });
        });
    </script>
@endpush
