@php use Carbon\Carbon; use Illuminate\Support\Str; $rawEvents = collect($events ?? [])->sortBy('timestamp_timestamp')->values(); $timelineRows = collect($eventTimelineHours ?? []); $screenshots = collect($eventScreenshots ?? []); $activeApps = collect($eventActiveApps ?? []); $activeWebsites = collect($eventActiveWebsites ?? []); $networkLogs = collect($eventNetworkLogs ?? []); $appsSummary = $eventAppsSummary ?? []; $formatTimestamp = function (?int $timestamp): string { if (!$timestamp) { return '—'; } return Carbon::createFromTimestamp($timestamp, company()->timezone)->format(company()->date_format . ' ' . company()->time_format); }; $formatTime = function (?int $timestamp): string { if (!$timestamp) { return '—'; } return Carbon::createFromTimestamp($timestamp, company()->timezone)->format(company()->time_format); }; $toneClasses = function (string $tone = 'gray'): array {
    return match ($tone) {
        'green' => [
            'panel' => 'border-success bg-light-success',
            'soft' => 'badge badge-success',
            'dot' => 'bg-success',
            'bar' => 'progress-bar-success',
            'text' => 'text-success',
        ],
        'amber' => [
            'panel' => 'border-warning bg-light-warning',
            'soft' => 'badge badge-warning',
            'dot' => 'bg-warning',
            'bar' => 'progress-bar-warning',
            'text' => 'text-warning',
        ],
        'orange' => [
            'panel' => 'border-warning bg-light-warning',
            'soft' => 'badge badge-warning',
            'dot' => 'bg-warning',
            'bar' => 'progress-bar-warning',
            'text' => 'text-warning',
        ],
        'red' => [
            'panel' => 'border-danger bg-light-danger',
            'soft' => 'badge badge-danger',
            'dot' => 'bg-danger',
            'bar' => 'progress-bar-danger',
            'text' => 'text-danger',
        ],
        'emerald' => [
            'panel' => 'border-success bg-light-success',
            'soft' => 'badge badge-success',
            'dot' => 'bg-success',
            'bar' => 'progress-bar-success',
            'text' => 'text-success',
        ],
        'sky' => [
            'panel' => 'border-primary bg-additional-grey',
            'soft' => 'badge badge-primary',
            'dot' => 'bg-primary',
            'bar' => 'progress-bar-primary',
            'text' => 'text-primary',
        ],
        default => [
            'panel' => 'border-grey bg-additional-grey',
            'soft' => 'badge badge-secondary',
            'dot' => 'bg-secondary',
            'bar' => 'progress-bar-primary',
            'text' => 'text-dark-grey',
        ],
    };
};
$classifyWorkBucket = function (string $value): string { $haystack = strtolower($value); if (str_contains($haystack, 'vscode') || str_contains($haystack, 'visual studio') || str_contains($haystack, 'cursor') || str_contains($haystack, 'phpstorm') || str_contains($haystack, 'intellij') || str_contains($haystack, 'webstorm') || str_contains($haystack, 'pycharm') || str_contains($haystack, 'terminal') || str_contains($haystack, 'git') || str_contains($haystack, 'code')) { return 'Development'; } if (str_contains($haystack, 'docs') || str_contains($haystack, 'documentation') || str_contains($haystack, 'confluence') || str_contains($haystack, 'notion') || str_contains($haystack, 'readme') || str_contains($haystack, 'guide') || str_contains($haystack, 'manual')) { return 'Documentation'; } if (str_contains($haystack, 'research') || str_contains($haystack, 'google') || str_contains($haystack, 'search') || str_contains($haystack, 'stackoverflow') || str_contains($haystack, 'wikipedia') || str_contains($haystack, 'browser') || str_contains($haystack, 'chrome') || str_contains($haystack, 'firefox') || str_contains($haystack, 'edge') || str_contains($haystack, 'safari')) { return 'Research'; } if (str_contains($haystack, 'slack') || str_contains($haystack, 'teams') || str_contains($haystack, 'outlook') || str_contains($haystack, 'mail') || str_contains($haystack, 'gmail') || str_contains($haystack, 'whatsapp') || str_contains($haystack, 'discord') || str_contains($haystack, 'zoom') || str_contains($haystack, 'meet')) { return 'Communication'; } if (str_contains($haystack, 'calendar') || str_contains($haystack, 'meetings') || str_contains($haystack, 'zoom meeting') || str_contains($haystack, 'teams meeting')) { return 'Meetings'; } return 'Other'; }; $classifyEventBucket = function (array $event): string { $type = strtolower((string) ($event['event_type'] ?? '')); $group = strtolower((string) ($event['activity_group'] ?? '')); $severity = strtolower((string) ($event['severity'] ?? 'info')); if (in_array($type, ['session_started', 'session_ended'], true)) { return 'focus'; } if (in_array($type, ['pause_started', 'idle_period'], true) || $group === 'idle') { return 'idle'; } if ($type === 'pause_ended' || $group === 'resume') { return 'resume'; } if (Str::contains($type, ['screenshot'])) { return 'screenshot'; } if (Str::contains($type, ['website', 'browser', 'url'])) { return 'website'; } if (Str::contains($type, ['app', 'application']) || $group === 'work start/stop') { return 'application'; } if (in_array($type, ['tamper_detected', 'agent_error', 'usb_connected'], true)) { return 'system'; } if (in_array($severity, ['critical', 'high'], true) || in_array($type, ['large_upload_detected', 'cloud_upload_detected'], true)) { return 'attention'; } if (in_array($type, ['tamper_detected', 'agent_error', 'usb_connected', 'large_upload_detected', 'cloud_upload_detected'], true)) { return 'system'; } return 'all'; }; $eventBadgeTone = function (?string $value): string { return match ($value) { 'Positive' => 'green', 'Warning' => 'amber', 'Critical' => 'red', default => 'gray', }; }; $flattenAppSessions = $activeApps->flatMap(function (array $app) { return collect($app['sessions'] ?? [])->map(function (array $session) use ($app) { return [ 'app_name' => $app['app_name'] ?? ($session['process_name'] ?? 'Unknown'), 'category_label' => $session['category_label'] ?? ($app['category_label'] ?? 'Neutral'), 'duration_label' => $session['duration_label'] ?? '0m', 'started_timestamp' => (int) ($session['started_timestamp'] ?? 0), 'icon_url' => $app['icon_url'] ?? null, 'letter_avatar' => $app['letter_avatar'] ?? null, 'trend_label' => $app['trend_vs_average_label'] ?? 'Within normal range', ]; }); })->sortByDesc('started_timestamp')->values(); $flattenWebsiteSessions = $activeWebsites->flatMap(function (array $site) { return collect($site['sessions'] ?? [])->map(function (array $session) use ($site) { $domain = $site['display_name'] ?? (parse_url((string) ($session['url'] ?? ''), PHP_URL_HOST) ?: 'Unknown'); return [ 'domain' => $domain, 'website_type' => $site['website_type'] ?? ($site['category_label'] ?? 'Other'), 'duration_label' => $session['duration_label'] ?? ($site['duration_label'] ?? '0m'), 'started_timestamp' => (int) ($session['started_timestamp'] ?? 0), 'url' => $session['url'] ?? null, 'window_title' => $session['window_title'] ?? null, ]; }); })->sortByDesc('started_timestamp')->values(); $flattenScreenshots = $screenshots->map(function (array $shot) { return [ 'id' => $shot['id'] ?? null, 'captured_timestamp' => (int) ($shot['captured_timestamp'] ?? 0), 'captured_time' => $shot['captured_time'] ?? '—', 'captured_at' => $shot['captured_at'] ?? '—', 'active_app' => $shot['active_app'] ?? '—', 'window_title' => $shot['window_title'] ?? '—', 'productivity_label' => $shot['productivity_label'] ?? 'Neutral', 'productivity_tone' => $shot['productivity_tone'] ?? 'amber', 'task_heading' => $shot['task_heading'] ?? null, 'task_project' => $shot['task_project'] ?? null, 'thumbnail_url' => $shot['thumbnail_url'] ?? null, 'full_url' => $shot['full_url'] ?? null, ]; })->sortByDesc('captured_timestamp')->values(); $eventRows = $rawEvents->map(function (array $event, int $index) use ($rawEvents, $timelineRows, $flattenAppSessions, $flattenWebsiteSessions, $flattenScreenshots, $formatTimestamp, $formatTime, $classifyEventBucket, $eventBadgeTone) { $timestamp = (int) ($event['timestamp_timestamp'] ?? 0); $eventTime = $formatTime($timestamp); $eventDateTime = $formatTimestamp($timestamp); $eventHour = $timestamp ? (int) Carbon::createFromTimestamp($timestamp, company()->timezone)->format('G') : null; $closestScreenshot = $flattenScreenshots->sortBy(fn (array $shot) => abs(((int) ($shot['captured_timestamp'] ?? 0)) - $timestamp))->first(); $timelineSegment = $eventHour !== null ? $timelineRows->firstWhere('hour', $eventHour) : null; $relatedApps = $flattenAppSessions ->filter(fn (array $session) => $timestamp > 0 && abs((int) ($session['started_timestamp'] ?? 0) - $timestamp) <= 3600) ->take(2) ->values(); if ($relatedApps->isEmpty()) { $relatedApps = $flattenAppSessions->take(2)->values(); } $relatedWebsites = $flattenWebsiteSessions ->filter(fn (array $session) => $timestamp > 0 && abs((int) ($session['started_timestamp'] ?? 0) - $timestamp) <= 3600) ->take(2) ->values(); if ($relatedWebsites->isEmpty()) { $relatedWebsites = $flattenWebsiteSessions->take(2)->values(); } $previousEvent = $rawEvents->get($index - 1); $nextEvent = $rawEvents->get($index + 1); $bucket = $classifyEventBucket($event); $severityTone = $eventBadgeTone($event['category'] ?? null); return [ 'id' => $event['id'] ?? $index, 'event_type' => $event['event_type'] ?? 'event', 'label' => $event['label'] ?? 'Event', 'icon' => $event['icon'] ?? 'info-circle', 'icon_color' => $event['icon_color'] ?? ' ', 'timestamp' => $eventDateTime, 'timestamp_time' => $eventTime, 'timestamp_timestamp' => $timestamp, 'category' => $event['category'] ?? 'Informational', 'severity' => $event['severity'] ?? 'info', 'severity_label' => $event['severity_label'] ?? 'Info', 'severity_tone' => $event['severity_tone'] ?? 'gray', 'activity_group' => $event['activity_group'] ?? 'Informational', 'related_application' => $event['related_application'] ?? 'System', 'duration_label' => $event['duration_label'] ?? '—', 'detail' => $event['detail'] ?? '—', 'payload' => $event['payload'] ?? '—', 'filter_bucket' => $bucket, 'tone' => $severityTone, 'closest_screenshot' => $closestScreenshot, 'related_timeline' => $timelineSegment ? [ 'hour' => $timelineSegment['hour'] ?? null, 'label' => $timelineSegment['label'] ?? '—', 'primary_app' => $timelineSegment['primary_app'] ?? null, 'primary_category' => $timelineSegment['primary_category'] ?? null, 'active_seconds' => $timelineSegment['active_seconds'] ?? 0, 'idle_seconds' => $timelineSegment['idle_seconds'] ?? 0, 'context_switches' => $timelineSegment['context_switches'] ?? 0, 'productivity_score' => $timelineSegment['productivity_score'] ?? 0, 'stats' => $timelineSegment['stats'] ?? [], ] : null, 'related_apps' => $relatedApps->values()->all(), 'related_websites' => $relatedWebsites->values()->all(), 'previous_event' => $previousEvent ? [ 'label' => $previousEvent['label'] ?? 'Event', 'time' => $formatTime((int) ($previousEvent['timestamp_timestamp'] ?? 0)), 'detail' => $previousEvent['detail'] ?? '—', ] : null, 'next_event' => $nextEvent ? [ 'label' => $nextEvent['label'] ?? 'Event', 'time' => $formatTime((int) ($nextEvent['timestamp_timestamp'] ?? 0)), 'detail' => $nextEvent['detail'] ?? '—', ] : null, ]; })->values(); $workStartedEvent = $eventRows->firstWhere('event_type', 'session_started') ?? $eventRows->first(); $workEndedEvent = $eventRows->filter(fn (array $event) => $event['event_type'] === 'session_ended')->last() ?? $eventRows->last(); $idleEventsCount = (int) $eventRows->filter(fn (array $event) => in_array($event['event_type'], ['pause_started', 'idle_period'], true))->count(); $attentionEventsCount = (int) $eventRows->filter(fn (array $event) => in_array($event['severity'], ['high', 'critical'], true))->count(); $importantEvents = $eventRows->filter(fn (array $event) => ($event['severity'] ?? 'info') !== 'info' || ($event['activity_group'] ?? 'Informational') !== 'Informational')->values(); $focusSessions = []; $currentFocusSession = null; foreach ($timelineRows as $row) { $score = (int) ($row['productivity_score'] ?? 0); $active = (int) ($row['active_seconds'] ?? 0); $isFocus = $score >= 65 && $active > 0; $hour = (int) ($row['hour'] ?? 0); $apps = collect($row['segments'] ?? [])->where('type', 'app')->pluck('app_name')->filter()->values(); if ($isFocus) { if (!$currentFocusSession) { $currentFocusSession = [ 'start_hour' => $hour, 'end_hour' => $hour, 'seconds' => $active, 'apps' => $apps, 'row' => $row, ]; } elseif ($hour === (($currentFocusSession['end_hour'] ?? $hour) + 1)) { $currentFocusSession['end_hour'] = $hour; $currentFocusSession['seconds'] += $active; $currentFocusSession['apps'] = $currentFocusSession['apps']->merge($apps)->unique()->values(); $currentFocusSession['row'] = $row; } else { $focusSessions[] = $currentFocusSession; $currentFocusSession = [ 'start_hour' => $hour, 'end_hour' => $hour, 'seconds' => $active, 'apps' => $apps, 'row' => $row, ]; } } elseif ($currentFocusSession) { $focusSessions[] = $currentFocusSession; $currentFocusSession = null; } } if ($currentFocusSession) { $focusSessions[] = $currentFocusSession; } $focusSessions = collect($focusSessions)->map(function (array $session) use ($timelineRows, $classifyWorkBucket) { $startRow = $timelineRows->firstWhere('hour', $session['start_hour']) ?? []; $endRow = $timelineRows->firstWhere('hour', $session['end_hour']) ?? []; $apps = collect($session['apps'] ?? [])->filter()->values(); $theme = $classifyWorkBucket($apps->first() ?? ''); return [ 'start_label' => $startRow['label'] ?? '—', 'end_label' => $endRow['label'] ?? '—', 'duration_label' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($session['seconds'] ?? 0)), 'app_line' => $apps->isNotEmpty() ? $apps->take(3)->implode(', ') : 'Focused work', 'theme' => $theme, 'seconds' => (int) ($session['seconds'] ?? 0), ]; })->sortByDesc('seconds')->values(); $idleSessions = []; $currentIdleSession = null; foreach ($timelineRows as $row) { $hour = (int) ($row['hour'] ?? 0); $idle = (int) ($row['idle_seconds'] ?? 0); $isIdle = $idle > 0; if ($isIdle) { if (!$currentIdleSession) { $currentIdleSession = [ 'start_hour' => $hour, 'end_hour' => $hour, 'seconds' => $idle, ]; } elseif ($hour === (($currentIdleSession['end_hour'] ?? $hour) + 1)) { $currentIdleSession['end_hour'] = $hour; $currentIdleSession['seconds'] += $idle; } else { $idleSessions[] = $currentIdleSession; $currentIdleSession = [ 'start_hour' => $hour, 'end_hour' => $hour, 'seconds' => $idle, ]; } } elseif ($currentIdleSession) { $idleSessions[] = $currentIdleSession; $currentIdleSession = null; } } if ($currentIdleSession) { $idleSessions[] = $currentIdleSession; } $idleSessions = collect($idleSessions)->map(function (array $session) use ($timelineRows) { $startRow = $timelineRows->firstWhere('hour', $session['start_hour']) ?? []; $endRow = $timelineRows->firstWhere('hour', $session['end_hour']) ?? []; return [ 'start_label' => $startRow['label'] ?? '—', 'end_label' => $endRow['label'] ?? '—', 'duration_label' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) ($session['seconds'] ?? 0)), 'seconds' => (int) ($session['seconds'] ?? 0), ]; })->sortByDesc('seconds')->values(); $longestFocusSession = $focusSessions->first(); $longestIdleSession = $idleSessions->first(); $averageFocusDuration = $focusSessions->isNotEmpty() ? \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) round($focusSessions->avg('seconds'))) : '0m'; $contextSwitchCount = (int) $timelineRows->sum('context_switches'); $interruptionCount = (int) max($idleEventsCount, $idleSessions->count()); $consistencyScore = (int) round((float) ($timelineRows->where('total_seconds', '>', 0)->avg('productivity_score') ?? 0)); $workdayScore = (int) max(0, min(100, round(100 - ($attentionEventsCount * 12) - ($idleEventsCount * 4) - min(18, $contextSwitchCount * 1.2) + min(12, $focusSessions->count() * 2)))); $workdayLabel = match (true) { $workdayScore >= 90 => 'Excellent', $workdayScore >= 75 => 'Good', $workdayScore >= 60 => 'Needs Attention', default => 'Critical', }; $workdayTone = match (true) { $workdayScore >= 90 => 'green', $workdayScore >= 75 => 'emerald', $workdayScore >= 60 => 'amber', default => 'red', }; $eventCategoryBase = collect([ 'Work Start/Stop' => (int) $eventRows->filter(fn (array $event) => in_array($event['event_type'], ['session_started', 'session_ended'], true))->count(), 'Idle' => $idleEventsCount, 'Resume' => (int) $eventRows->filter(fn (array $event) => $event['event_type'] === 'pause_ended')->count(), 'Focus Session' => (int) $focusSessions->count(), 'Application Change' => (int) $eventRows->filter(fn (array $event) => Str::contains(strtolower((string) ($event['event_type'] ?? '')), ['app']) || Str::contains(strtolower((string) ($event['label'] ?? '')), ['app']))->count(), 'Website Activity' => (int) $activeWebsites->count(), 'Screenshot Event' => (int) $screenshots->count(), 'System Event' => (int) $eventRows->filter(fn (array $event) => in_array($event['event_type'], ['tamper_detected', 'agent_error', 'usb_connected'], true))->count(), 'Alert Event' => (int) $eventRows->filter(fn (array $event) => in_array($event['event_type'], ['large_upload_detected', 'cloud_upload_detected'], true) || in_array($event['severity'], ['high', 'critical'], true))->count(), ]); $eventCategoryTotal = max(1, (int) $eventCategoryBase->sum()); $eventTypeCounts = $eventCategoryBase->map(function (int $count, string $label) use ($eventCategoryTotal) { return [ 'label' => $label, 'count' => $count, 'pct' => round(($count / $eventCategoryTotal) * 100, 1), ]; })->filter(fn (array $item) => $item['count'] > 0)->sortByDesc('count')->values(); $positiveSignals = []; if ($focusSessions->isNotEmpty()) { $positiveSignals[] = ['label' => 'Long focus blocks', 'detail' => $longestFocusSession['duration_label'] ?? '0m', 'tone' => 'green']; } if ($idleEventsCount <= 2) { $positiveSignals[] = ['label' => 'Low idle time', 'detail' => $idleEventsCount . ' idle event(s)', 'tone' => 'green']; } if ($workdayScore >= 75) { $positiveSignals[] = ['label' => 'Consistent work rhythm', 'detail' => 'The day stayed steady', 'tone' => 'green']; } $attentionSignals = []; if ($attentionEventsCount > 0) { $attentionSignals[] = ['label' => 'Attention events detected', 'detail' => $attentionEventsCount . ' event(s) require review', 'tone' => 'amber']; } if ($longestIdleSession && ($longestIdleSession['seconds'] ?? 0) >= 20 * 60) { $attentionSignals[] = ['label' => 'Long idle period', 'detail' => $longestIdleSession['duration_label'] . ' idle', 'tone' => 'amber']; } if ($contextSwitchCount >= 12) { $attentionSignals[] = ['label' => 'Frequent context switching', 'detail' => $contextSwitchCount . ' switches recorded', 'tone' => 'amber']; } $topAppsLabel = $flattenAppSessions->take(3)->pluck('app_name')->filter()->implode(', '); $topWebsitesLabel = $flattenWebsiteSessions->take(3)->pluck('domain')->filter()->implode(', '); $aiSummary = $appsSummary['ai_summary'] ?? 'No unusual activity detected.'; if ($attentionEventsCount > 0 && $longestIdleSession) { $aiSummary = 'The employee maintained activity throughout the day, but ' . $attentionEventsCount . ' attention event(s) and a longest idle block of ' . ($longestIdleSession['duration_label'] ?? '0m') . ' suggest a quick review is worthwhile.'; } elseif ($focusSessions->isNotEmpty() && $idleEventsCount <= 2) { $aiSummary = 'The day shows healthy work rhythm with repeated focus blocks and low interruption. Most activity centered around ' . ($topAppsLabel !== '' ? $topAppsLabel : 'core work applications') . '.'; } elseif ($topAppsLabel !== '') { $aiSummary = 'Most activity centered around ' . $topAppsLabel . '.'; if ($topWebsitesLabel !== '') { $aiSummary .= ' Web activity also flowed through ' . $topWebsitesLabel . '.'; } $aiSummary .= ' No unusual pattern detected.'; } $filterCounts = [ 'all' => $eventRows->count(), 'attention' => $eventRows->filter(fn (array $event) => $event['filter_bucket'] === 'attention')->count(), 'idle' => $eventRows->filter(fn (array $event) => $event['filter_bucket'] === 'idle')->count(), 'focus' => $eventRows->filter(fn (array $event) => $event['filter_bucket'] === 'focus')->count(), 'system' => $eventRows->filter(fn (array $event) => $event['filter_bucket'] === 'system')->count(), ]; $importantTimeline = $eventRows->filter(fn (array $event) => in_array($event['filter_bucket'], ['attention', 'idle', 'focus', 'system', 'resume'], true) || in_array($event['severity'], ['high', 'critical'], true))->values(); $attentionReview = $eventRows->filter(fn (array $event) => in_array($event['filter_bucket'], ['attention', 'idle', 'system'], true) || in_array($event['severity'], ['high', 'critical'], true))->unique('event_type')->values()->take(5); $currentStatusLabel = $appsSummary['current_status_label'] ?? 'Active'; $currentStatusTone = $appsSummary['current_status_tone'] ?? 'green'; $currentAppLabel = $appsSummary['current_app'] ?? 'No active app'; $currentSessionLabel = $appsSummary['current_session_duration_label'] ?? '0m'; $currentActivityLabel = $appsSummary['current_activity_label'] ?? 'No activity recorded yet'; $applicationHealthScore = (int) ($appsSummary['application_health_score'] ?? $workdayScore); [$applicationHealthLabel, $applicationHealthTone] = [ $appsSummary['application_health_label'] ?? $workdayLabel, $appsSummary['application_health_tone'] ?? $workdayTone, ]; $mostRecentEvent = $eventRows->last(); $eventDrawerData = $eventRows->map(function (array $event) { return $event; })->keyBy('id'); 
    $iconWrapperClass = function (?string $iconColor): string {
        $value = (string) ($iconColor ?? '');
        if (str_contains($value, 'red')) {
            return 'text-danger bg-light-danger';
        }
        if (str_contains($value, 'yellow') || str_contains($value, 'orange')) {
            return 'text-warning bg-light-warning';
        }
        if (str_contains($value, 'purple')) {
            return 'text-primary bg-additional-grey';
        }

        return 'text-primary bg-additional-grey';
    };

    $themeClass = function (string $tone) use ($toneClasses): array { return $toneClasses($tone); };
@endphp

<div class="p-20">
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Daily Activity Summary</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">A compact view of the day so managers can see the shape of the workday in seconds.</p>
                        </div>
                        <span class="badge badge-secondary">{{ $importantTimeline->count() }} important events</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        @foreach ([
                            ['label' => 'Work Started', 'value' => $workStartedEvent['timestamp_time'] ?? '—', 'tone' => 'green'],
                            ['label' => 'Work Ended', 'value' => $workEndedEvent['timestamp_time'] ?? '—', 'tone' => 'gray'],
                            ['label' => 'Idle Events', 'value' => number_format($idleEventsCount), 'tone' => $idleEventsCount > 0 ? 'amber' : 'green'],
                            ['label' => 'Focus Sessions', 'value' => number_format($focusSessions->count()), 'tone' => 'emerald'],
                            ['label' => 'Attention Events', 'value' => number_format($attentionEventsCount), 'tone' => $attentionEventsCount > 0 ? 'red' : 'green'],
                            ['label' => 'Total Important Events', 'value' => number_format($importantTimeline->count()), 'tone' => 'gray'],
                        ] as $summaryCard)
                            @php($cardTone = $themeClass($summaryCard['tone']))
                            <div class="col-lg-4 col-md-6 p-20 border-bottom-grey border-right-grey">
                                <p class="f-11 text-lightest text-uppercase mb-2">{{ $summaryCard['label'] }}</p>
                                <div class="f-16 f-w-500 text-darkest-grey">{{ $summaryCard['value'] }}</div>
                                <div class="progress mt-3" style="height:6px;">
                                    <div class="progress-bar {{ $cardTone['bar'] }}" style="width:100%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">AI Activity Summary</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Conclusions first, data second. This is the manager's quick read on the day.</p>
                        </div>
                        <div class="d-flex flex-wrap">
                            <span class="badge badge-secondary mr-2 mb-1">{{ $currentStatusLabel }}</span>
                            <span class="badge badge-success mr-2 mb-1">{{ $focusSessions->count() }} focus sessions</span>
                            <span class="badge badge-warning mb-1">{{ $attentionEventsCount }} attention events</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-20">
                    <div class="bg-grey rounded p-20 mb-3">
                        <p class="f-14 text-dark-grey mb-0">{{ $aiSummary }}</p>
                    </div>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-secondary mr-2 mb-2">Current app: {{ $currentAppLabel }}</span>
                        <span class="badge badge-secondary mr-2 mb-2">Current session: {{ $currentSessionLabel }}</span>
                        @if ($topAppsLabel !== '')
                            <span class="badge badge-success mr-2 mb-2">Top apps: {{ $topAppsLabel }}</span>
                        @endif
                        @if ($topWebsitesLabel !== '')
                            <span class="badge badge-primary mb-2">Top websites: {{ $topWebsitesLabel }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3 border-left-warning">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Attention Required</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Events that may need manager review right now.</p>
                        </div>
                        <span class="badge badge-warning">{{ count($attentionReview) }} items to review</span>
                    </div>
                </div>
                <div class="card-body p-20">
                    @if ($attentionReview->isNotEmpty())
                        <div class="row">
                            @foreach ($attentionReview as $event)
                                @php($attentionTone = $themeClass($event['tone'] ?? 'amber'))
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <button type="button" class="btn btn-block text-left border-grey rounded bg-white p-3 h-100" data-event-open data-event-id="{{ $event['id'] }}">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="d-flex align-items-start">
                                                <span class="d-flex align-items-center justify-content-center rounded mr-3 {{ $iconWrapperClass($event['icon_color'] ?? '') }}" style="width:40px;height:40px;">
                                                    <i class="fa fa-{{ $event['icon'] ?? 'info-circle' }}" aria-hidden="true"></i>
                                                </span>
                                                <div>
                                                    <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $event['label'] }}</p>
                                                    <p class="f-12 text-lightest mb-0 mt-1">{{ $event['timestamp_time'] }} · {{ $event['related_application'] }}</p>
                                                </div>
                                            </div>
                                            <span class="{{ $attentionTone['soft'] }}">{{ $event['severity_label'] }}</span>
                                        </div>
                                        <div class="f-12 text-dark-grey mt-3 text-left">
                                            <p class="mb-1"><span class="f-w-500 text-darkest-grey">Issue:</span> {{ $event['detail'] }}</p>
                                            <p class="mb-0"><span class="f-w-500 text-darkest-grey">Category:</span> {{ $event['activity_group'] }}</p>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-grey rounded p-20 f-14 text-dark-grey text-center mb-0">No attention items found for the selected day.</div>
                    @endif
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Important Events Timeline</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">A playback-style view of how the day unfolded.</p>
                        </div>
                        <div class="d-flex flex-wrap">
                            <span class="badge badge-success mr-2 mb-1">{{ $focusSessions->count() }} focus sessions</span>
                            <span class="badge badge-warning mb-1">{{ $idleEventsCount }} idle events</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-20">
                    @forelse ($importantTimeline as $event)
                        @php($eventTone = $themeClass($event['tone'] ?? 'gray'))
                        <button type="button" class="btn btn-block text-left border-grey rounded bg-white p-3 mb-3" data-event-open data-event-id="{{ $event['id'] }}">
                            <div class="d-flex flex-wrap align-items-start justify-content-between">
                                <div class="d-flex align-items-start">
                                    <span class="d-flex align-items-center justify-content-center rounded mr-3 {{ $iconWrapperClass($event['icon_color'] ?? '') }}" style="width:40px;height:40px;">
                                        <i class="fa fa-{{ $event['icon'] ?? 'info-circle' }}" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <p class="f-11 text-lightest text-uppercase mb-1">{{ $event['timestamp_time'] }}</p>
                                        <h4 class="f-14 f-w-500 text-darkest-grey mb-1">{{ $event['label'] }}</h4>
                                        <p class="f-12 text-lightest mb-0">{{ $event['detail'] }}</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap">
                                    <span class="{{ $eventTone['soft'] }} mr-2 mb-1">{{ $event['severity_label'] }}</span>
                                    <span class="badge badge-secondary mb-1">{{ $event['activity_group'] }}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap mt-3">
                                <span class="badge badge-secondary mr-2 mb-1">Related app: {{ $event['related_application'] }}</span>
                                <span class="badge badge-secondary mr-2 mb-1">Duration: {{ $event['duration_label'] }}</span>
                                <span class="badge badge-secondary mb-1">Status: {{ $event['category'] }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="bg-grey rounded p-20 f-14 text-lightest text-center mb-0">No important events recorded for this day.</div>
                    @endforelse
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Focus Sessions</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Major uninterrupted blocks of productive work.</p>
                        </div>
                        <div class="card-body p-20">
                            @forelse ($focusSessions->take(4) as $session)
                                @php($sessionTone = $themeClass($session['theme'] ?? 'gray'))
                                <div class="border-grey rounded p-3 mb-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <p class="f-11 text-lightest text-uppercase mb-1">Focus Session</p>
                                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">{{ $session['start_label'] }} - {{ $session['end_label'] }}</h4>
                                        </div>
                                        <span class="{{ $sessionTone['soft'] }}">{{ $session['duration_label'] }}</span>
                                    </div>
                                    <p class="f-14 text-dark-grey mt-3 mb-0">{{ $session['app_line'] }}</p>
                                </div>
                            @empty
                                <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No clear focus sessions found for this day.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100 mb-0">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Activity Pattern Analysis</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">A quick measure of workflow quality and interruptions.</p>
                        </div>
                        <div class="card-body p-20">
                            @foreach ([
                                ['label' => 'Longest Focus Session', 'value' => $longestFocusSession['duration_label'] ?? '0m', 'tone' => 'green'],
                                ['label' => 'Longest Idle Session', 'value' => $longestIdleSession['duration_label'] ?? '0m', 'tone' => 'amber'],
                                ['label' => 'Average Focus Duration', 'value' => $averageFocusDuration, 'tone' => 'emerald'],
                                ['label' => 'Context Switch Count', 'value' => number_format($contextSwitchCount), 'tone' => 'gray'],
                                ['label' => 'Interruption Count', 'value' => number_format($interruptionCount), 'tone' => 'red'],
                                ['label' => 'Work Consistency Score', 'value' => $consistencyScore . '/100', 'tone' => $consistencyScore >= 75 ? 'green' : ($consistencyScore >= 60 ? 'amber' : 'red')],
                            ] as $metric)
                                @php($metricTone = $themeClass($metric['tone']))
                                <div class="border rounded p-3 mb-3 {{ $metricTone['panel'] }}">
                                    <p class="f-11 text-lightest text-uppercase mb-1">{{ $metric['label'] }}</p>
                                    <p class="f-16 f-w-500 text-darkest-grey mb-0">{{ $metric['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Event Categories</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Grouped by business meaning instead of raw system type.</p>
                </div>
                <div class="card-body p-20">
                    @forelse ($eventTypeCounts as $bucket)
                        @php($bucketTone = match ($bucket['label']) {
                            'Work Start/Stop', 'Focus Session' => 'green',
                            'Resume', 'Website Activity', 'Screenshot Event' => 'sky',
                            'Idle', 'System Event' => 'amber',
                            'Alert Event' => 'red',
                            default => 'gray',
                        })
                        @php($bucketClasses = $themeClass($bucketTone))
                        <div class="border-grey rounded p-3 mb-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $bucket['label'] }}</p>
                                    <p class="f-12 text-lightest mb-0 mt-1">{{ number_format($bucket['count']) }} events</p>
                                </div>
                                <span class="{{ $bucketClasses['soft'] }}">{{ $bucket['pct'] }}%</span>
                            </div>
                            <div class="progress mt-3" style="height:8px;">
                                <div class="progress-bar {{ $bucketClasses['bar'] }}" style="width:{{ max(4, min(100, $bucket['pct'])) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No category data available.</p>
                    @endforelse
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Detailed Event Log</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Raw records stay available for investigation, but stay out of the way by default.</p>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" data-events-toggle>
                            <i class="fa fa-plus mr-1" aria-hidden="true"></i>
                            <span>Expand detailed event log</span>
                        </button>
                    </div>
                </div>
                <div class="d-none card-body p-20" data-events-panel>
                    <div class="bg-white border-grey rounded p-3 mb-3 d-flex flex-wrap">
                        @foreach ([
                            'all' => 'All Events',
                            'attention' => 'Attention Only',
                            'idle' => 'Idle Events',
                            'focus' => 'Focus Sessions',
                            'system' => 'System Events',
                        ] as $filterKey => $label)
                            @php($isActiveFilter = $filterKey === 'all')
                            <button type="button" class="btn {{ $isActiveFilter ? 'btn-primary' : 'btn-secondary' }} btn-sm mr-2 mb-2" data-event-filter="{{ $filterKey }}">
                                {{ $label }}
                                <span class="badge badge-light ml-1">{{ number_format($filterCounts[$filterKey] ?? 0) }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="table-responsive border-grey rounded">
                        <table class="table table-hover w-100 mb-0">
                            <thead>
                                <tr class="border-bottom-grey">
                                    <th class="f-11 text-lightest text-uppercase">Time</th>
                                    <th class="f-11 text-lightest text-uppercase">Event Type</th>
                                    <th class="f-11 text-lightest text-uppercase">Category</th>
                                    <th class="f-11 text-lightest text-uppercase">Severity</th>
                                    <th class="f-11 text-lightest text-uppercase">Related Application</th>
                                    <th class="f-11 text-lightest text-uppercase">Duration</th>
                                    <th class="f-11 text-lightest text-uppercase">Status</th>
                                    <th class="f-11 text-lightest text-uppercase">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($eventRows as $event)
                                    @php($rowTone = $themeClass($event['tone'] ?? 'gray'))
                                    <tr data-event-open data-event-id="{{ $event['id'] }}" data-event-filter-bucket="{{ $event['filter_bucket'] }}" style="cursor:pointer;">
                                        <td class="text-nowrap f-14 f-w-500 text-darkest-grey">{{ $event['timestamp_time'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="d-flex align-items-center justify-content-center rounded mr-2 {{ $iconWrapperClass($event['icon_color'] ?? '') }}" style="width:32px;height:32px;">
                                                    <i class="fa fa-{{ $event['icon'] ?? 'info-circle' }}" aria-hidden="true"></i>
                                                </span>
                                                <div>
                                                    <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $event['label'] }}</p>
                                                    <p class="f-12 text-lightest mb-0">{{ $event['activity_group'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-secondary">{{ $event['activity_group'] }}</span></td>
                                        <td><span class="{{ $rowTone['soft'] }}">{{ $event['severity_label'] }}</span></td>
                                        <td class="f-14 text-dark-grey">{{ $event['related_application'] }}</td>
                                        <td class="text-nowrap f-14 text-dark-grey">{{ $event['duration_label'] }}</td>
                                        <td><span class="badge badge-secondary">{{ $event['category'] }}</span></td>
                                        <td class="f-14 text-dark-grey">{{ $event['detail'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center f-14 text-lightest py-4">No events available for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Workday Health</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Manager summary and live context for the selected day.</p>
                </div>
                <div class="card-body p-20">
                    @php($workdayClasses = $themeClass($workdayTone))
                    <div class="rounded border p-3 mb-3 {{ $workdayClasses['panel'] }}">
                        <p class="f-11 text-lightest text-uppercase mb-2">Workday Health</p>
                        <div class="d-flex align-items-end">
                            <span class="f-21 font-weight-bold text-darkest-grey">{{ $workdayScore }}</span>
                            <span class="{{ $workdayClasses['soft'] }} ml-2 mb-1">{{ $workdayLabel }}</span>
                        </div>
                        <div class="progress mt-3" style="height:8px;">
                            <div class="progress-bar {{ $workdayClasses['bar'] }}" style="width:{{ $workdayScore }}%"></div>
                        </div>
                    </div>
                    @foreach ([
                        ['label' => 'Current Status', 'value' => $currentStatusLabel, 'tone' => $currentStatusTone],
                        ['label' => 'Current App', 'value' => $currentAppLabel, 'tone' => 'gray'],
                        ['label' => 'Current Activity', 'value' => $currentActivityLabel, 'tone' => 'gray'],
                        ['label' => 'Current Session', 'value' => $currentSessionLabel, 'tone' => 'gray'],
                        ['label' => 'Attention Count', 'value' => number_format($attentionEventsCount), 'tone' => $attentionEventsCount > 0 ? 'amber' : 'green'],
                        ['label' => 'Last Event', 'value' => $mostRecentEvent['timestamp_time'] ?? '—', 'tone' => 'gray'],
                        ['label' => 'Longest Focus Block', 'value' => $longestFocusSession['duration_label'] ?? '0m', 'tone' => 'green'],
                        ['label' => 'Longest Idle Block', 'value' => $longestIdleSession['duration_label'] ?? '0m', 'tone' => $longestIdleSession ? 'amber' : 'gray'],
                        ['label' => 'Productivity Score', 'value' => $applicationHealthScore . '/100', 'tone' => $applicationHealthTone],
                    ] as $metric)
                        @php($metricTone = $themeClass($metric['tone']))
                        <div class="border-grey rounded p-3 mb-3">
                            <p class="f-11 text-lightest text-uppercase mb-1">{{ $metric['label'] }}</p>
                            <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $metric['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-none" data-event-drawer style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:1050;">
    <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);" data-event-drawer-close></div>
    <div class="bg-white b-shadow-4" style="position:absolute;top:0;right:0;height:100%;width:100%;max-width:640px;overflow-y:auto;">
        <div class="border-bottom-grey bg-white p-20" style="position:sticky;top:0;z-index:1;">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="f-11 text-lightest text-uppercase mb-1">Event Investigation</p>
                    <h3 class="f-16 f-w-500 text-darkest-grey mb-0" data-event-drawer-title>Event</h3>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-event-drawer-close>
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="p-20">
            <div class="border-grey rounded p-3 mb-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between">
                    <div>
                        <p class="f-11 text-lightest text-uppercase mb-1" data-event-drawer-time>—</p>
                        <h4 class="f-16 f-w-500 text-darkest-grey mb-0" data-event-drawer-subtitle>Details</h4>
                    </div>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-secondary mr-2 mb-1" data-event-drawer-category>Informational</span>
                        <span class="badge badge-secondary mb-1" data-event-drawer-severity>Info</span>
                    </div>
                </div>
                <p class="f-14 text-dark-grey mt-3 mb-0" data-event-drawer-detail>—</p>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="border-grey rounded bg-white p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">Related Screenshot</p>
                        <div class="border-grey rounded overflow-hidden mb-3">
                            <img src="" alt="" class="d-none w-100" style="height:176px;object-fit:cover;" data-event-drawer-screenshot>
                            <div class="d-flex align-items-center justify-content-center f-14 text-lightest" style="height:176px;" data-event-drawer-screenshot-empty>No screenshot nearby</div>
                        </div>
                        <p class="f-w-500 text-darkest-grey mb-1" data-event-drawer-screenshot-app>—</p>
                        <p class="f-14 text-dark-grey mb-1" data-event-drawer-screenshot-window>—</p>
                        <p class="f-12 text-lightest mb-0" data-event-drawer-screenshot-meta>—</p>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="border-grey rounded bg-white p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">Related Timeline Segment</p>
                        <p class="f-w-500 text-darkest-grey mb-1" data-event-drawer-timeline-title>—</p>
                        <p class="f-14 text-dark-grey mb-1" data-event-drawer-timeline-meta>—</p>
                        <p class="f-12 text-lightest mb-0" data-event-drawer-timeline-stats>—</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="border-grey rounded bg-white p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">Related Application Activity</p>
                        <div data-event-drawer-apps></div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="border-grey rounded bg-white p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">Related Website Activity</p>
                        <div data-event-drawer-websites></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="border-grey rounded bg-white p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">Previous Event</p>
                        <p class="f-w-500 text-darkest-grey mb-1" data-event-drawer-prev-title>—</p>
                        <p class="f-12 text-lightest mb-2" data-event-drawer-prev-time>—</p>
                        <p class="f-14 text-dark-grey mb-0" data-event-drawer-prev-detail>—</p>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="border-grey rounded bg-white p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">Next Event</p>
                        <p class="f-w-500 text-darkest-grey mb-1" data-event-drawer-next-title>—</p>
                        <p class="f-12 text-lightest mb-2" data-event-drawer-next-time>—</p>
                        <p class="f-14 text-dark-grey mb-0" data-event-drawer-next-detail>—</p>
                    </div>
                </div>
            </div>
            <div class="border-grey rounded p-3">
                <p class="f-11 text-lightest text-uppercase mb-2">Raw Payload</p>
                <p class="f-14 text-dark-grey mb-0" style="white-space:pre-wrap;" data-event-drawer-payload>—</p>
            </div>
        </div>
    </div>
</div>

@push('scripts') <script> (function () { const events = @json($eventDrawerData->values()); const byId = Object.fromEntries(events.map(event => [String(event.id), event])); const escapeHtml = (value) => String(value ?? '') .replace(/&/g, '&amp;') .replace(/</g, '&lt;') .replace(/>/g, '&gt;') .replace(/"/g, '&quot;') .replace(/'/g, '&#039;'); const $drawer = $('[data-event-drawer]').first(); const $drawerTitle = $('[data-event-drawer-title]').first(); const $drawerTime = $('[data-event-drawer-time]').first(); const $drawerSubtitle = $('[data-event-drawer-subtitle]').first(); const $drawerDetail = $('[data-event-drawer-detail]').first(); const $drawerCategory = $('[data-event-drawer-category]').first(); const $drawerSeverity = $('[data-event-drawer-severity]').first(); const $drawerPayload = $('[data-event-drawer-payload]').first(); const $screenshot = $('[data-event-drawer-screenshot]').first(); const $screenshotEmpty = $('[data-event-drawer-screenshot-empty]').first(); const $screenshotApp = $('[data-event-drawer-screenshot-app]').first(); const $screenshotWindow = $('[data-event-drawer-screenshot-window]').first(); const $screenshotMeta = $('[data-event-drawer-screenshot-meta]').first(); const $timelineTitle = $('[data-event-drawer-timeline-title]').first(); const $timelineMeta = $('[data-event-drawer-timeline-meta]').first(); const $timelineStats = $('[data-event-drawer-timeline-stats]').first(); const $apps = $('[data-event-drawer-apps]').first(); const $websites = $('[data-event-drawer-websites]').first(); const $prevTitle = $('[data-event-drawer-prev-title]').first(); const $prevTime = $('[data-event-drawer-prev-time]').first(); const $prevDetail = $('[data-event-drawer-prev-detail]').first(); const $nextTitle = $('[data-event-drawer-next-title]').first(); const $nextTime = $('[data-event-drawer-next-time]').first(); const $nextDetail = $('[data-event-drawer-next-detail]').first(); const openDrawer = (event) => { if (!event) { return; } $drawerTitle.text(event.label || 'Event'); $drawerTime.text(event.timestamp || '—'); $drawerSubtitle.text(`${event.related_application || 'System'} · ${event.activity_group || 'Informational'}`); $drawerDetail.text(event.detail || '—'); $drawerCategory.text(event.activity_group || 'Informational'); $drawerSeverity.text(event.severity_label || 'Info'); $drawerPayload.text(event.payload || '—'); if (event.closest_screenshot && event.closest_screenshot.thumbnail_url) { $screenshot.attr('src', event.closest_screenshot.thumbnail_url).removeClass('d-none'); $screenshotEmpty.addClass('d-none'); $screenshotApp.text(event.closest_screenshot.active_app || '—'); $screenshotWindow.text(event.closest_screenshot.window_title || '—'); $screenshotMeta.text(`${event.closest_screenshot.captured_at || '—'} · ${event.closest_screenshot.productivity_label || 'Neutral'}`); } else { $screenshot.attr('src', '').addClass('d-none'); $screenshotEmpty.removeClass('d-none'); $screenshotApp.text('—'); $screenshotWindow.text('—'); $screenshotMeta.text('—'); } if (event.related_timeline) { const timeline = event.related_timeline; $timelineTitle.text(timeline.primary_app || timeline.label || 'Timeline segment'); $timelineMeta.text(`${timeline.label || '—'} · ${timeline.primary_category || 'Neutral'}`); $timelineStats.text(`Active: ${timeline.active_seconds || 0}s · Idle: ${timeline.idle_seconds || 0}s · Switches: ${timeline.context_switches || 0} · Score: ${timeline.productivity_score || 0}/100`); } else { $timelineTitle.text('—'); $timelineMeta.text('—'); $timelineStats.text('—'); } const appsHtml = Array.isArray(event.related_apps) && event.related_apps.length ? event.related_apps.map(app => ` <div class="mb-2"> <p class="f-w-500 text-darkest-grey">${escapeHtml(app.app_name || 'Unknown app')}</p> <p class="f-12 text-lightest">${escapeHtml(app.duration_label || '0m')} · ${escapeHtml(app.category_label || 'Neutral')} · ${escapeHtml(app.trend_label || 'Within normal range')}</p> </div> `).join('') : '<p class="f-14 text-lightest">No nearby application activity found.</p>'; $apps.html(appsHtml); const websitesHtml = Array.isArray(event.related_websites) && event.related_websites.length ? event.related_websites.map(site => ` <div class="mb-2"> <p class="f-w-500 text-darkest-grey">${escapeHtml(site.domain || 'Unknown site')}</p> <p class="f-12 text-lightest">${escapeHtml(site.duration_label || '0m')} · ${escapeHtml(site.website_type || 'Other')}</p> </div> `).join('') : '<p class="f-14 text-lightest">No nearby website activity found.</p>'; $websites.html(websitesHtml); if (event.previous_event) { $prevTitle.text(event.previous_event.label || 'Event'); $prevTime.text(event.previous_event.time || '—'); $prevDetail.text(event.previous_event.detail || '—'); } else { $prevTitle.text('—'); $prevTime.text('—'); $prevDetail.text('—'); } if (event.next_event) { $nextTitle.text(event.next_event.label || 'Event'); $nextTime.text(event.next_event.time || '—'); $nextDetail.text(event.next_event.detail || '—'); } else { $nextTitle.text('—'); $nextTime.text('—'); $nextDetail.text('—'); } $drawer.removeClass('d-none'); $('body').addClass('overflow-hidden'); }; const closeDrawer = () => { $drawer.addClass('d-none'); $('body').removeClass('overflow-hidden'); }; $('body').off('click.monitorEventsOpen').on('click.monitorEventsOpen', '[data-event-open]', function () { const id = $(this).data('event-id'); const event = byId[String(id)]; openDrawer(event); }); $('body').off('click.monitorEventsClose').on('click.monitorEventsClose', '[data-event-drawer-close]', function () { closeDrawer(); }); $('body').off('keydown.monitorEventsEsc').on('keydown.monitorEventsEsc', function (e) { if (e.key === 'Escape') { closeDrawer(); } }); $('body').off('click.monitorEventsToggle').on('click.monitorEventsToggle', '[data-events-toggle]', function () { const $panel = $('[data-events-panel]').first(); const expanded = $panel.hasClass('d-none'); $panel.toggleClass('d-none', !expanded); $(this).find('span').text(expanded ? 'Collapse detailed event log' : 'Expand detailed event log'); $(this).find('i').toggleClass('fa-plus', !expanded).toggleClass('fa-minus', expanded); }); $('body').off('click.monitorEventsFilter').on('click.monitorEventsFilter', '[data-event-filter]', function () { const filter = ($(this).data('event-filter') || 'all').toString(); $('[data-event-filter]').removeClass('btn-primary').addClass('btn-secondary'); $(this).removeClass('btn-secondary').addClass('btn-primary'); $('[data-event-filter-bucket]').each(function () { const bucket = ($(this).data('event-filter-bucket') || 'all').toString(); const show = filter === 'all' || bucket === filter || (filter === 'attention' && bucket === 'attention') || (filter === 'idle' && bucket === 'idle') || (filter === 'focus' && bucket === 'focus') || (filter === 'system' && bucket === 'system'); $(this).toggleClass('d-none', !show); }); }); })(); </script>
@endpush
