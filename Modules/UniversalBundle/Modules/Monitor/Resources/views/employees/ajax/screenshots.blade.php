@php
    use Illuminate\Support\Str;

    $shots = collect($screenshots ?? [])->values();
    $screenshotFilters = $screenshotFilters ?? [
        'task' => $screenshotTaskFilter ?? 'all',
        'app' => '',
        'project' => '',
        'category' => '',
        'productivity' => '',
        'search' => '',
        'attention' => false,
        'idle' => false,
    ];
    $taskFilterValue = (string) ($screenshotFilters['task'] ?? $screenshotTaskFilter ?? 'all');
    $appFilterValue = (string) ($screenshotFilters['app'] ?? '');
    $projectFilterValue = (string) ($screenshotFilters['project'] ?? '');
    $categoryFilterValue = (string) ($screenshotFilters['category'] ?? '');
    $productivityFilterValue = (string) ($screenshotFilters['productivity'] ?? '');
    $searchFilterValue = (string) ($screenshotFilters['search'] ?? '');
    $attentionFilterValue = (bool) ($screenshotFilters['attention'] ?? false);
    $idleFilterValue = (bool) ($screenshotFilters['idle'] ?? false);

    $orderedShots = $shots->sortByDesc('captured_timestamp')->values();
    $orderedShotsAsc = $shots->sortBy('captured_timestamp')->values();
    $totalShots = $orderedShots->count();

    $isFlaggedShot = function (array $shot): bool {
        $category = (string) ($shot['category'] ?? '');
        $haystack = strtolower(implode(' ', array_filter([
            $shot['active_app'] ?? '',
            $shot['window_title'] ?? '',
            $shot['task_heading'] ?? '',
            $shot['task_project'] ?? '',
        ])));
        $suspiciousKeywords = ['youtube', 'facebook', 'instagram', 'tiktok', 'reddit', 'netflix', 'prime video', 'primevideo', 'gaming', 'casino', 'torrent', 'adult'];

        return $category === 'unproductive'
            || !$haystack
            || Str::contains($haystack, $suspiciousKeywords)
            || Str::contains($haystack, ['incognito', 'private browsing', 'private mode', 'unknown application']);
    };

    $isIdleShot = function (array $shot): bool {
        $haystack = strtolower(implode(' ', array_filter([
            $shot['active_app'] ?? '',
            $shot['window_title'] ?? '',
        ])));

        return $haystack === '' || Str::contains($haystack, ['idle', 'locked', 'screen saver', 'screensaver']);
    };

    $formatGap = function (?int $seconds): string {
        if (!$seconds || $seconds <= 0) {
            return 'First capture';
        }

        return \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($seconds) . ' after previous screenshot';
    };

    $categoryTone = function (?string $category): string {
        return match ($category) {
            'productive' => 'green',
            'unproductive' => 'red',
            default => 'amber',
        };
    };

    $categoryClass = function (?string $category): string {
        return match ($category) {
            'productive' => 'badge-success',
            'unproductive' => 'badge-danger',
            default => 'badge-warning',
        };
    };

    $screenshotCounts = [
        'productive' => (int) $orderedShots->where('category', 'productive')->count(),
        'neutral' => (int) $orderedShots->where('category', 'neutral')->count(),
        'unproductive' => (int) $orderedShots->where('category', 'unproductive')->count(),
    ];

    $attentionShots = $orderedShots->filter($isFlaggedShot);
    $idleShots = $orderedShots->filter($isIdleShot);

    $appCounts = $orderedShots
        ->pluck('active_app')
        ->filter()
        ->countBy()
        ->sortDesc();

    $projectCounts = $orderedShots
        ->pluck('task_project')
        ->filter()
        ->countBy()
        ->sortDesc();

    $mostActiveApp = $appCounts->keys()->first() ?: 'No active app';
    $mostActiveAppCount = (int) ($appCounts->first() ?? 0);
    $mostActiveProject = $projectCounts->keys()->first() ?: 'No project linked';
    $mostActiveProjectCount = (int) ($projectCounts->first() ?? 0);

    $productiveShots = (int) $screenshotCounts['productive'];
    $neutralShots = (int) $screenshotCounts['neutral'];
    $attentionCount = (int) $attentionShots->count();

    $evidenceHealth = $totalShots > 0
        ? (int) max(0, min(100, round((($productiveShots * 100) + ($neutralShots * 65) + (($totalShots - $attentionCount) * 15)) / max(1, $totalShots * 100) * 100)))
        : 0;

    $evidenceTone = match (true) {
        $evidenceHealth >= 90 => 'green',
        $evidenceHealth >= 75 => 'emerald',
        $evidenceHealth >= 60 => 'amber',
        default => 'red',
    };

    $evidenceLabel = match (true) {
        $evidenceHealth >= 90 => 'Excellent',
        $evidenceHealth >= 75 => 'Good',
        $evidenceHealth >= 60 => 'Needs Review',
        default => 'Critical',
    };

    $latestShot = $orderedShots->first();
    $latestProject = $latestShot['task_project'] ?? 'No project linked';
    $latestTask = $latestShot['task_heading'] ?? 'No task linked';
    $latestApp = $latestShot['active_app'] ?? 'No active app';
    $latestTitle = $latestShot['window_title'] ?? '';
    $latestTimestamp = $latestShot['captured_at'] ?? '—';
    $latestScoreTone = $categoryTone($latestShot['category'] ?? null);

    $activeProjectShots = $orderedShots->filter(fn ($shot) => !empty($shot['task_project']));

    $sessionGroups = [];
    $session = null;

    foreach ($orderedShotsAsc as $shot) {
        $capturedAt = (int) ($shot['captured_timestamp'] ?? 0);

        if (!$session) {
            $session = [
                'start' => $shot,
                'end' => $shot,
                'shots' => [$shot],
            ];
            continue;
        }

        $previousTimestamp = (int) ($session['end']['captured_timestamp'] ?? 0);
        $gap = $capturedAt - $previousTimestamp;

        if ($gap <= 45 * 60) {
            $session['shots'][] = $shot;
            $session['end'] = $shot;
            continue;
        }

        $sessionGroups[] = $session;
        $session = [
            'start' => $shot,
            'end' => $shot,
            'shots' => [$shot],
        ];
    }

    if ($session) {
        $sessionGroups[] = $session;
    }

    $workSessions = collect($sessionGroups)->map(function (array $group) use ($categoryTone, $categoryClass) {
        $shotsInGroup = collect($group['shots'] ?? []);
        $start = $group['start'];
        $end = $group['end'];
        $durationSeconds = max(60, ((int) ($end['captured_timestamp'] ?? 0)) - ((int) ($start['captured_timestamp'] ?? 0)));
        $apps = $shotsInGroup->pluck('active_app')->filter()->countBy()->sortDesc();
        $projects = $shotsInGroup->pluck('task_project')->filter()->countBy()->sortDesc();
        $categories = $shotsInGroup->pluck('category')->filter()->countBy()->sortDesc();
        $dominantCategory = $categories->keys()->first() ?: ($shotsInGroup->first()['category'] ?? 'neutral');
        $dominantApp = $apps->keys()->first() ?: 'Focused work';

        $sessionType = match (true) {
            Str::contains(strtolower($dominantApp), ['codex', 'vscode', 'phpstorm', 'cursor', 'terminal', 'git', 'code']) => 'Development Session',
            Str::contains(strtolower(implode(' ', $projects->keys()->all())), ['research', 'docs', 'notion', 'wiki']) => 'Research Session',
            Str::contains(strtolower($dominantApp), ['slack', 'teams', 'mail', 'gmail', 'outlook', 'zoom', 'meet']) => 'Communication Session',
            default => 'Work Session',
        };

        return [
            'label' => $sessionType,
            'start_label' => $start['captured_time'] ?? $start['captured_at'] ?? '—',
            'end_label' => $end['captured_time'] ?? $end['captured_at'] ?? '—',
            'duration_label' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration($durationSeconds),
            'shots_count' => $shotsInGroup->count(),
            'apps' => $apps->keys()->take(3)->values(),
            'project' => $projects->keys()->first() ?: 'No project linked',
            'project_count' => (int) ($projects->first() ?? 0),
            'category' => $dominantCategory,
            'category_badge' => $categoryClass($dominantCategory),
            'category_tone' => $categoryTone($dominantCategory),
            'primary_app' => $dominantApp,
        ];
    })->sortByDesc('shots_count')->values();

    $timelineHours = collect(range(7, 23))->map(function (int $hour) use ($orderedShots) {
        $hourShots = $orderedShots->filter(function (array $shot) use ($hour) {
            $capturedAt = \Carbon\Carbon::createFromTimestamp((int) ($shot['captured_timestamp'] ?? 0), company()->timezone);

            return (int) $capturedAt->format('G') === $hour;
        });

        $hourCount = $hourShots->count();
        $productive = (int) $hourShots->where('category', 'productive')->count();
        $neutral = (int) $hourShots->where('category', 'neutral')->count();
        $unproductive = (int) $hourShots->where('category', 'unproductive')->count();
        $count = max(1, $hourCount);
        $score = (int) round((($productive * 100) + ($neutral * 65) + ($unproductive * 20)) / $count);

        $level = match (true) {
            $hourCount === 0 => 'idle',
            $score >= 80 => 'high',
            $score >= 55 => 'medium',
            default => 'low',
        };

        return [
            'hour' => $hour,
            'label' => \Carbon\Carbon::createFromTime($hour, 0, 0, company()->timezone)->format('g A'),
            'count' => $hourShots->count(),
            'score' => $score,
            'level' => $level,
        ];
    });

    $distribution = collect([
        ['label' => 'Productive', 'count' => $productiveShots, 'tone' => 'green', 'class' => 'progress-bar-success'],
        ['label' => 'Neutral', 'count' => $neutralShots, 'tone' => 'amber', 'class' => 'progress-bar-warning'],
        ['label' => 'Attention', 'count' => $attentionCount, 'tone' => 'red', 'class' => 'progress-bar-danger'],
    ])->map(function (array $item) use ($totalShots) {
        $item['pct'] = $totalShots > 0 ? round(($item['count'] / $totalShots) * 100, 1) : 0;

        return $item;
    });

    $filteredApps = $appCounts->keys()->values()->take(16);
    $filteredProjects = $projectCounts->keys()->values()->take(16);

    $workdaySummary = $totalShots > 0
        ? (
            $attentionCount > 0
                ? 'Screenshots show most work happening in ' . $mostActiveApp . ' and ' . $mostActiveProject . '. ' . $attentionCount . ' screenshot(s) require review, but the overall day still shows a clear work pattern.'
                : 'Screenshots show a consistent work pattern centered on ' . $mostActiveApp . ' and ' . $mostActiveProject . '. No unusual activity detected.'
        )
        : 'No screenshots available for this date.';

    $searchHaystack = fn (array $shot) => strtolower(implode(' ', array_filter([
        $shot['captured_at'] ?? '',
        $shot['captured_time'] ?? '',
        $shot['active_app'] ?? '',
        $shot['window_title'] ?? '',
        $shot['task_heading'] ?? '',
        $shot['task_project'] ?? '',
        $shot['productivity_label'] ?? '',
        $shot['category'] ?? '',
    ])));

    $evidenceBadge = match ($evidenceTone) {
        'green', 'emerald' => 'badge-success',
        'amber' => 'badge-warning',
        'red' => 'badge-danger',
        default => 'badge-secondary',
    };
@endphp

<div class="p-20">
    <div class="card bg-white border-0 b-shadow-4 mb-3">
        <div class="card-header bg-white border-bottom-grey p-20">
            <div class="d-flex flex-wrap align-items-start justify-content-between">
                <div>
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Screenshot Summary</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">A compact read on how the work evidence looks today.</p>
                </div>
                <span class="badge {{ $evidenceBadge }}">{{ $evidenceLabel }}</span>
            </div>
        </div>
        <div class="card-body p-20">
            <div class="row">
                @foreach ([
                    ['label' => 'Screenshots Captured', 'value' => number_format($totalShots)],
                    ['label' => 'Productive', 'value' => number_format($productiveShots)],
                    ['label' => 'Neutral', 'value' => number_format($neutralShots)],
                    ['label' => 'Attention', 'value' => number_format($attentionCount), 'tone' => 'red'],
                    ['label' => 'Most Active App', 'value' => $mostActiveApp, 'sub' => $mostActiveAppCount ? number_format($mostActiveAppCount) . ' shots' : '—'],
                    ['label' => 'Most Active Project', 'value' => $mostActiveProject, 'sub' => $mostActiveProjectCount ? number_format($mostActiveProjectCount) . ' shots' : '—'],
                ] as $metric)
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="bg-grey rounded p-3 h-100">
                            <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                            <div class="f-16 f-w-500 text-darkest-grey mt-2">{{ $metric['value'] }}</div>
                            @if (!empty($metric['sub']))
                                <div class="f-12 text-lightest mt-1">{{ $metric['sub'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">AI Daily Summary</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Conclusions first, screenshots second.</p>
                </div>
                <div class="card-body p-20">
                    <div class="bg-grey rounded p-20 mb-3">
                        <p class="f-14 text-dark-grey mb-0">“{{ $workdaySummary }}”</p>
                    </div>
                    <div class="d-flex flex-wrap">
                        <span class="badge badge-secondary mr-2 mb-2">Evidence health: {{ number_format($evidenceHealth) }}/100</span>
                        <span class="badge badge-secondary mr-2 mb-2">Productive screenshots: {{ number_format($productiveShots) }}</span>
                        <span class="badge badge-secondary mb-2">Attention screenshots: {{ number_format($attentionCount) }}</span>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Screenshot Timeline</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Jump into the day at a glance before scrolling the gallery.</p>
                </div>
                <div class="card-body p-20">
                    <div class="row">
                        @foreach ($timelineHours as $hour)
                            @php
                                $hourBarClass = match ($hour['level']) {
                                    'high' => 'progress-bar-success',
                                    'medium' => 'progress-bar-warning',
                                    'low' => 'progress-bar-danger',
                                    default => 'bg-additional-grey',
                                };
                            @endphp
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                <div class="bg-grey rounded p-3 text-center h-100">
                                    <div class="f-11 text-lightest text-uppercase">{{ $hour['label'] }}</div>
                                    <div class="f-16 f-w-500 text-darkest-grey mt-2">{{ number_format((int) $hour['count']) }}</div>
                                    <div class="progress mt-3" style="height:10px;">
                                        <div class="progress-bar {{ $hourBarClass }}" style="width: {{ max(8, min(100, $hour['score'] ?: 25)) }}%; opacity: {{ max(30, min(100, $hour['score'] ?: 25)) / 100 }};"></div>
                                    </div>
                                    <div class="f-12 f-w-500 text-darkest-grey mt-2">{{ $hour['count'] ? number_format($hour['score']) . '/100' : '—' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <form class="card bg-white border-0 b-shadow-4 mb-3 sticky-top" method="GET" action="{{ route('monitor.show', ['monitor' => $employee->id]) }}" style="top:0;z-index:20;">
                <input type="hidden" name="tab" value="screenshots">
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div>
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Smart Filters</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Filters stay visible while you scan the evidence.</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fa fa-filter mr-1"></i> @lang('app.apply')
                            </button>
                            <a href="{{ route('monitor.show', ['monitor' => $employee->id, 'tab' => 'screenshots', 'date' => $selectedDate]) }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-times-circle mr-1" aria-hidden="true"></i> @lang('app.clearFilters')
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-20">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label for="screenshot-task-filter" class="f-12 f-w-500 text-lightest text-uppercase d-block mb-2">@lang('app.task')</label>
                            <select id="screenshot-task-filter" name="task" class="form-control select-picker" data-size="8" data-container="body">
                                <option value="all" @selected($taskFilterValue === 'all')>@lang('app.all')</option>
                                <option value="none" @selected($taskFilterValue === 'none')>@lang('monitor::app.noTaskLinked')</option>
                                @foreach ($screenshotTaskOptions as $taskOption)
                                    <option value="{{ $taskOption['id'] }}" @selected((string) $taskFilterValue === (string) $taskOption['id'])>
                                        {{ $taskOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label for="screenshot-app-filter" class="f-12 f-w-500 text-lightest text-uppercase d-block mb-2">Application</label>
                            <select id="screenshot-app-filter" name="app" class="form-control select-picker" data-size="8" data-container="body">
                                <option value="">All applications</option>
                                @foreach ($filteredApps as $appName)
                                    <option value="{{ $appName }}" @selected($appFilterValue === $appName)>{{ $appName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label for="screenshot-project-filter" class="f-12 f-w-500 text-lightest text-uppercase d-block mb-2">Project</label>
                            <select id="screenshot-project-filter" name="project" class="form-control select-picker" data-size="8" data-container="body">
                                <option value="">All projects</option>
                                @foreach ($filteredProjects as $projectName)
                                    <option value="{{ $projectName }}" @selected($projectFilterValue === $projectName)>{{ $projectName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label for="screenshot-category-filter" class="f-12 f-w-500 text-lightest text-uppercase d-block mb-2">Category</label>
                            <select id="screenshot-category-filter" name="category" class="form-control select-picker" data-size="8" data-container="body">
                                <option value="">All categories</option>
                                <option value="productive" @selected($categoryFilterValue === 'productive')>Productive</option>
                                <option value="neutral" @selected($categoryFilterValue === 'neutral')>Neutral</option>
                                <option value="unproductive" @selected($categoryFilterValue === 'unproductive')>Attention</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label for="screenshot-productivity-filter" class="f-12 f-w-500 text-lightest text-uppercase d-block mb-2">Productivity</label>
                            <select id="screenshot-productivity-filter" name="productivity" class="form-control select-picker" data-size="8" data-container="body">
                                <option value="">All productivity labels</option>
                                <option value="productive" @selected($productivityFilterValue === 'productive')>Productive</option>
                                <option value="neutral" @selected($productivityFilterValue === 'neutral')>Neutral</option>
                                <option value="attention" @selected($productivityFilterValue === 'attention')>Attention</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <label for="screenshot-search" class="f-12 f-w-500 text-lightest text-uppercase d-block mb-2">Search</label>
                            <input id="screenshot-search" name="search" value="{{ $searchFilterValue }}" type="search" class="form-control height-35 f-14" placeholder="Search app, window title, task, or project">
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 d-flex align-items-end">
                            <label class="checkbox checkbox-info mb-0">
                                <input id="screenshot-attention-only" name="attention" value="1" type="checkbox" @checked($attentionFilterValue)>
                                <span class="f-14 text-dark-grey">Attention only</span>
                            </label>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 d-flex align-items-end">
                            <label class="checkbox checkbox-info mb-0">
                                <input id="screenshot-idle-only" name="idle" value="1" type="checkbox" @checked($idleFilterValue)>
                                <span class="f-14 text-dark-grey">Idle screenshots</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Evidence Gallery</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">Screenshots stay front and center. Tap one to open the evidence drawer.</p>
                </div>
                <div class="card-body p-20">
                    @if ($orderedShots->isEmpty())
                        <div class="bg-grey rounded p-20 text-center">
                            <span class="d-flex align-items-center justify-content-center rounded-circle bg-white mb-3" style="width:40px;height:40px;">
                                <i class="fa fa-camera text-lightest" aria-hidden="true"></i>
                            </span>
                            <p class="f-14 text-lightest mb-0">@lang('monitor::app.noScreenshots')</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach ($orderedShots as $index => $shot)
                                @php
                                    $previousShot = $orderedShots->get($index + 1);
                                    $gapSeconds = $previousShot
                                        ? max(0, (int) ($shot['captured_timestamp'] ?? 0) - (int) ($previousShot['captured_timestamp'] ?? 0))
                                        : null;
                                    $gapLabel = $formatGap($gapSeconds);
                                    $isAttention = $isFlaggedShot($shot);
                                    $isIdle = $isIdleShot($shot);
                                    $searchText = $searchHaystack($shot);
                                    $related = $orderedShots->filter(function (array $other) use ($shot) {
                                        if ($other['id'] === $shot['id']) {
                                            return false;
                                        }

                                        return ($other['task_project'] ?? null) === ($shot['task_project'] ?? null)
                                            || ($other['active_app'] ?? null) === ($shot['active_app'] ?? null);
                                    })->take(3);
                                @endphp
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                    <button type="button"
                                        class="js-screenshot-card btn btn-block p-0 text-left border-grey rounded b-shadow-4 bg-white"
                                        data-index="{{ $index }}"
                                        data-screenshot-id="{{ $shot['id'] }}"
                                        data-image-url="{{ $shot['full_url'] }}"
                                        data-thumbnail-url="{{ $shot['thumbnail_url'] }}"
                                        data-active-app="{{ $shot['active_app'] ?? '' }}"
                                        data-window-title="{{ $shot['window_title'] ?? '' }}"
                                        data-captured-at="{{ $shot['captured_at'] ?? '' }}"
                                        data-captured-timestamp="{{ $shot['captured_timestamp'] ?? 0 }}"
                                        data-category="{{ $shot['category'] ?? 'neutral' }}"
                                        data-productivity-label="{{ $shot['productivity_label'] ?? 'Neutral' }}"
                                        data-productivity-tone="{{ $shot['productivity_tone'] ?? 'amber' }}"
                                        data-task-heading="{{ $shot['task_heading'] ?? '' }}"
                                        data-task-project="{{ $shot['task_project'] ?? '' }}"
                                        data-task-status="{{ $shot['task_status'] ?? '' }}"
                                        data-task-priority="{{ $shot['task_priority'] ?? '' }}"
                                        data-task-due-date="{{ $shot['task_due_date'] ?? '' }}"
                                        data-task-url="{{ $shot['task_url'] ?? '' }}"
                                        data-project-name="{{ $shot['project_name'] ?? '' }}"
                                        data-keystrokes="{{ (int) ($shot['interaction_stats']['keystrokes'] ?? 0) }}"
                                        data-clicks="{{ (int) ($shot['interaction_stats']['mouse_clicks'] ?? 0) }}"
                                        data-scrolls="{{ (int) ($shot['interaction_stats']['scroll_events'] ?? 0) }}"
                                        data-previous-captured-at="{{ $shot['previous_captured_at'] ?? '' }}"
                                        data-next-captured-at="{{ $shot['next_captured_at'] ?? '' }}"
                                        data-is-attention="{{ $isAttention ? 1 : 0 }}"
                                        data-is-idle="{{ $isIdle ? 1 : 0 }}"
                                        data-search-haystack="{{ $searchText }}"
                                    >
                                        <div style="position:relative;padding-bottom:75%;overflow:hidden;">
                                            <img src="{{ $shot['thumbnail_url'] }}"
                                                alt="{{ $shot['window_title'] ?? $shot['active_app'] }}"
                                                class="w-100"
                                                style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:top;"
                                                loading="lazy">
                                        </div>
                                        <div class="p-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="text-truncate pr-2">
                                                    <p class="f-12 text-lightest mb-1">
                                                        <i class="fa fa-clock mr-1" aria-hidden="true"></i>{{ $shot['captured_time'] ?? $shot['captured_at'] ?? '—' }}
                                                    </p>
                                                    <p class="text-truncate f-14 f-w-500 text-darkest-grey mb-0">{{ $shot['active_app'] ?? 'Unknown application' }}</p>
                                                </div>
                                                <span class="badge {{ $categoryClass($shot['category'] ?? null) }}">{{ $shot['productivity_label'] ?? 'Neutral' }}</span>
                                            </div>
                                            @if (!empty($shot['window_title']))
                                                <p class="f-12 text-dark-grey mt-2 mb-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;" title="{{ $shot['window_title'] }}">{{ $shot['window_title'] }}</p>
                                            @endif
                                            <div class="d-flex flex-wrap mt-2">
                                                @if (!empty($shot['task_heading']))
                                                    <span class="badge badge-primary mr-1 mb-1">Task: {{ Str::limit($shot['task_heading'], 28) }}</span>
                                                @endif
                                                @if (!empty($shot['task_project']))
                                                    <span class="badge badge-secondary mr-1 mb-1">Project: {{ Str::limit($shot['task_project'], 18) }}</span>
                                                @endif
                                                @if ($isAttention)
                                                    <span class="badge badge-danger mb-1">Requires review</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between border-top-grey pt-3 mt-3 f-12 text-lightest">
                                                <span>{{ $gapLabel }}</span>
                                                <span>{{ $shot['captured_date'] ?? '' }}</span>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Work Session Grouping</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Managers usually read the day by sessions, not individual shots.</p>
                        </div>
                        <div class="card-body p-20">
                            @forelse ($workSessions->take(4) as $session)
                                <div class="border-grey rounded p-3 mb-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="f-14 f-w-500 text-darkest-grey">{{ $session['label'] }}</div>
                                            <div class="f-12 text-lightest mt-1">{{ $session['start_label'] }} - {{ $session['end_label'] }} · {{ $session['shots_count'] }} screenshots</div>
                                        </div>
                                        <span class="badge {{ $session['category_badge'] }}">{{ $session['duration_label'] }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap mt-3">
                                        @foreach ($session['apps'] as $appName)
                                            <span class="badge badge-secondary mr-1 mb-1">{{ $appName }}</span>
                                        @endforeach
                                    </div>
                                    <div class="mt-3">
                                        <span class="badge {{ $categoryClass($session['category']) }}">{{ $session['project'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No session groups available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card bg-white border-0 b-shadow-4 h-100">
                        <div class="card-header bg-white border-bottom-grey p-20">
                            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Attention Review Queue</h4>
                            <p class="f-12 text-lightest mb-0 mt-1">Only flagged screenshots appear here.</p>
                        </div>
                        <div class="card-body p-20">
                            @forelse ($attentionShots->take(6) as $shot)
                                <button type="button"
                                    class="js-screenshot-card btn btn-block text-left border-grey rounded p-3 mb-3 bg-white"
                                    data-image-url="{{ $shot['full_url'] }}"
                                    data-thumbnail-url="{{ $shot['thumbnail_url'] }}"
                                    data-active-app="{{ $shot['active_app'] ?? '' }}"
                                    data-window-title="{{ $shot['window_title'] ?? '' }}"
                                    data-captured-at="{{ $shot['captured_at'] ?? '' }}"
                                    data-captured-timestamp="{{ $shot['captured_timestamp'] ?? 0 }}"
                                    data-category="{{ $shot['category'] ?? 'neutral' }}"
                                    data-productivity-label="{{ $shot['productivity_label'] ?? 'Neutral' }}"
                                    data-productivity-tone="{{ $shot['productivity_tone'] ?? 'amber' }}"
                                    data-task-heading="{{ $shot['task_heading'] ?? '' }}"
                                    data-task-project="{{ $shot['task_project'] ?? '' }}"
                                    data-task-status="{{ $shot['task_status'] ?? '' }}"
                                    data-task-priority="{{ $shot['task_priority'] ?? '' }}"
                                    data-task-due-date="{{ $shot['task_due_date'] ?? '' }}"
                                    data-task-url="{{ $shot['task_url'] ?? '' }}"
                                    data-project-name="{{ $shot['project_name'] ?? '' }}"
                                    data-keystrokes="{{ (int) ($shot['interaction_stats']['keystrokes'] ?? 0) }}"
                                    data-clicks="{{ (int) ($shot['interaction_stats']['mouse_clicks'] ?? 0) }}"
                                    data-scrolls="{{ (int) ($shot['interaction_stats']['scroll_events'] ?? 0) }}"
                                    data-is-attention="1"
                                    data-is-idle="{{ $isIdleShot($shot) ? 1 : 0 }}"
                                    data-search-haystack="{{ $searchHaystack($shot) }}"
                                >
                                    <div class="d-flex align-items-start">
                                        <img src="{{ $shot['thumbnail_url'] }}"
                                            alt="{{ $shot['window_title'] ?? $shot['active_app'] }}"
                                            class="rounded border-grey mr-3"
                                            style="width:96px;height:64px;object-fit:cover;object-position:top;">
                                        <div class="ml-3" style="min-width: 0; flex: 1;">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <div class="f-14 f-w-500 text-darkest-grey">{{ $shot['active_app'] ?? 'Unknown application' }}</div>
                                                    <div class="f-12 text-lightest mt-1">{{ $shot['captured_time'] ?? $shot['captured_at'] ?? '—' }}</div>
                                                </div>
                                                <span class="badge badge-danger">Review</span>
                                            </div>
                                            <div class="f-12 text-dark-grey mt-2 mb-0">{{ Str::limit($shot['window_title'] ?? 'No title available', 90) }}</div>
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No flagged screenshots to review.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div class="card-header bg-white border-bottom-grey p-20">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Productivity Distribution</h4>
                    <p class="f-12 text-lightest mb-0 mt-1">A quick understanding of how the evidence breaks down.</p>
                </div>
                <div class="card-body p-20">
                    @foreach ($distribution as $item)
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="f-14 f-w-500 text-darkest-grey">{{ $item['label'] }}</span>
                                <span class="f-12 text-lightest">{{ number_format($item['count']) }} · {{ number_format((float) $item['pct'], 1) }}%</span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar {{ $item['class'] }}" style="width: {{ $item['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div style="position:sticky;top:16px;">
                <div class="card bg-white border-0 b-shadow-4 mb-3">
                    <div class="card-header bg-white border-bottom-grey p-20">
                        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Evidence Health</h4>
                        <p class="f-12 text-lightest mb-0 mt-1">Sticky manager panel for quick context.</p>
                    </div>
                    <div class="card-body p-20">
                        <div class="bg-grey rounded p-20 mb-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="f-11 text-lightest text-uppercase">Evidence Health</div>
                                    <div class="f-21 f-w-500 text-darkest-grey mt-2">{{ number_format($evidenceHealth) }}/100</div>
                                    <div class="f-14 text-dark-grey mt-1">{{ $evidenceLabel }}</div>
                                </div>
                                <span class="badge {{ $evidenceBadge }}">{{ $evidenceLabel }}</span>
                            </div>
                            <div class="row mt-4">
                                <div class="col-sm-6 mb-3">
                                    <div class="bg-white rounded border-grey p-3">
                                        <div class="f-11 text-lightest text-uppercase">Current App</div>
                                        <div class="text-truncate f-w-500 text-darkest-grey mt-1">{{ $latestApp }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="bg-white rounded border-grey p-3">
                                        <div class="f-11 text-lightest text-uppercase">Current Task</div>
                                        <div class="text-truncate f-w-500 text-darkest-grey mt-1">{{ $latestTask }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="bg-white rounded border-grey p-3">
                                        <div class="f-11 text-lightest text-uppercase">Latest Screenshot</div>
                                        <div class="f-w-500 text-darkest-grey mt-1">{{ $latestTimestamp }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="bg-white rounded border-grey p-3">
                                        <div class="f-11 text-lightest text-uppercase">Screenshot Count</div>
                                        <div class="f-w-500 text-darkest-grey mt-1">{{ number_format($totalShots) }}</div>
                                    </div>
                                </div>
                            </div>
                            @if ($latestShot)
                                <div class="mt-2 overflow-hidden rounded border-grey bg-white">
                                    <img src="{{ $latestShot['thumbnail_url'] }}"
                                        alt="{{ $latestShot['window_title'] ?? $latestShot['active_app'] }}"
                                        class="w-100"
                                        style="height:192px;object-fit:cover;object-position:top;">
                                </div>
                            @endif
                        </div>

                        <div class="bg-grey rounded p-20 mb-3">
                            <div class="f-11 text-lightest text-uppercase">Quick Evidence Metrics</div>
                            <div class="mt-3">
                                @foreach ([
                                    ['label' => 'Productive screenshots', 'value' => number_format($productiveShots)],
                                    ['label' => 'Neutral screenshots', 'value' => number_format($neutralShots)],
                                    ['label' => 'Attention screenshots', 'value' => number_format($attentionCount)],
                                    ['label' => 'Most active app', 'value' => $mostActiveApp],
                                    ['label' => 'Most active project', 'value' => $mostActiveProject],
                                ] as $metric)
                                    <div class="d-flex align-items-center justify-content-between mb-2 f-14">
                                        <span class="text-lightest">{{ $metric['label'] }}</span>
                                        <span class="text-truncate f-w-500 text-darkest-grey ml-3" style="max-width:55%;">{{ $metric['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-grey rounded p-20">
                            <div class="f-11 text-lightest text-uppercase">Most recent activity</div>
                            <div class="mt-3 f-14 text-dark-grey">
                                <div class="f-w-500 text-darkest-grey">{{ $latestApp }}</div>
                                @if ($latestTitle)
                                    <div class="f-12 text-lightest mt-1">{{ $latestTitle }}</div>
                                @endif
                                <div class="f-12 text-lightest mt-1">{{ $latestProject }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-none" data-screenshot-drawer style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:1050;">
    <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.45);" data-screenshot-drawer-close></div>
    <aside class="bg-white b-shadow-4 d-flex flex-column" data-screenshot-drawer-panel style="position:absolute;top:0;right:0;height:100%;width:100%;max-width:42rem;">
        <div class="d-flex align-items-start justify-content-between border-bottom-grey p-20">
            <div>
                <div class="f-11 text-lightest text-uppercase">Screenshot Detail Drawer</div>
                <h4 class="f-16 f-w-500 text-darkest-grey mt-1 mb-0" data-drawer-title>Screenshot</h4>
            </div>
            <button type="button" class="btn btn-default btn-sm rounded-circle" data-screenshot-drawer-close style="width:36px;height:36px;">
                <i class="fa fa-times text-lightest" aria-hidden="true"></i>
            </button>
        </div>
        <div class="p-20" style="flex: 1; overflow-y: auto;">
            <div class="overflow-hidden rounded border-grey mb-4">
                <img src="" alt="" class="w-100" style="max-height:320px;object-fit:cover;object-position:top;" data-drawer-image>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="border-grey rounded p-3 mb-3">
                        <div class="d-flex flex-wrap align-items-start justify-content-between">
                            <div>
                                <div class="f-11 text-lightest text-uppercase">Timestamp</div>
                                <div class="f-14 f-w-500 text-darkest-grey mt-1" data-drawer-time>—</div>
                            </div>
                            <span class="badge badge-warning" data-drawer-category>Neutral</span>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-6 mb-3">
                                <div class="bg-grey rounded p-3">
                                    <div class="f-11 text-lightest text-uppercase">Application</div>
                                    <div class="f-w-500 text-darkest-grey mt-1" data-drawer-app>—</div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="bg-grey rounded p-3">
                                    <div class="f-11 text-lightest text-uppercase">Productivity</div>
                                    <div class="f-w-500 text-darkest-grey mt-1" data-drawer-productivity>—</div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="bg-grey rounded p-3">
                                    <div class="f-11 text-lightest text-uppercase">Keystrokes</div>
                                    <div class="f-w-500 text-darkest-grey mt-1" data-drawer-keystrokes>—</div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="bg-grey rounded p-3">
                                    <div class="f-11 text-lightest text-uppercase">Clicks</div>
                                    <div class="f-w-500 text-darkest-grey mt-1" data-drawer-clicks>—</div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="bg-grey rounded p-3">
                                    <div class="f-11 text-lightest text-uppercase">Scrolls</div>
                                    <div class="f-w-500 text-darkest-grey mt-1" data-drawer-scrolls>—</div>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="bg-grey rounded p-3">
                                    <div class="f-11 text-lightest text-uppercase">Project</div>
                                    <div class="f-w-500 text-darkest-grey mt-1" data-drawer-project>—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-grey rounded p-3 mb-3">
                        <div class="f-11 text-lightest text-uppercase">Window Title</div>
                        <div class="f-14 text-dark-grey mt-2" data-drawer-window-title>—</div>
                    </div>
                    <div class="border-grey rounded p-3">
                        <div class="f-11 text-lightest text-uppercase">Task</div>
                        <div class="f-14 f-w-500 text-darkest-grey mt-2" data-drawer-task>—</div>
                        <div class="d-flex flex-wrap mt-2">
                            <span class="d-none badge badge-secondary mr-1 mb-1" data-drawer-task-status></span>
                            <span class="d-none badge badge-secondary mr-1 mb-1" data-drawer-task-priority></span>
                            <span class="d-none badge badge-secondary mb-1" data-drawer-task-due-date></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="border-grey rounded p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="f-11 text-lightest text-uppercase">Previous Screenshot</div>
                                <div class="f-14 text-darkest-grey mt-1" data-drawer-previous>—</div>
                            </div>
                            <div class="text-right">
                                <div class="f-11 text-lightest text-uppercase">Next Screenshot</div>
                                <div class="f-14 text-darkest-grey mt-1" data-drawer-next>—</div>
                            </div>
                        </div>
                    </div>
                    <div class="border-grey rounded p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="f-11 text-lightest text-uppercase">Related Screenshots</div>
                            <span class="f-12 text-lightest">Same app or project</span>
                        </div>
                        <div class="row" data-drawer-related></div>
                    </div>
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-primary mr-2 mb-2" data-drawer-open-preview>
                            <i class="fa fa-external-link-alt mr-1 f-11" aria-hidden="true"></i> Open full preview
                        </button>
                        <button type="button" class="btn btn-secondary mb-2" data-screenshot-drawer-close>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

@push('scripts')
    <script>
        (function () {
            const previewBaseUrl = @json(route('monitor.screenshot.preview'));
            const employeeShowBase = @json(route('monitor.show', $employee->id));
            const selectedDate = @json($selectedDate);

            const $drawer = $('[data-screenshot-drawer]');
            const $cards = $('.js-screenshot-card');
            let activeCard = null;

            function buildScreenshotTabUrl(task) {
                let url = employeeShowBase + '?tab=screenshots&date=' + encodeURIComponent(selectedDate);
                if (task && task !== 'all') {
                    url += '&task=' + encodeURIComponent(task);
                }

                return url;
            }

            function openDrawer() {
                $drawer.removeClass('d-none');
            }

            function closeDrawer() {
                $drawer.addClass('d-none');
            }

            function getVisibleCards() {
                return $cards.filter(function () {
                    return !$(this).hasClass('d-none');
                }).toArray();
            }

            function collectCardData(el) {
                const $el = $(el);

                return {
                    el: el,
                    imageUrl: $el.data('image-url') || '',
                    thumbnailUrl: $el.data('thumbnail-url') || '',
                    activeApp: $el.data('active-app') || 'Unknown application',
                    windowTitle: $el.data('window-title') || '',
                    capturedAt: $el.data('captured-at') || '—',
                    category: $el.data('category') || 'neutral',
                    productivityLabel: $el.data('productivity-label') || 'Neutral',
                    productivityTone: $el.data('productivity-tone') || 'amber',
                    taskHeading: $el.data('task-heading') || '',
                    taskProject: $el.data('task-project') || '',
                    taskStatus: $el.data('task-status') || '',
                    taskPriority: $el.data('task-priority') || '',
                    taskDueDate: $el.data('task-due-date') || '',
                    taskUrl: $el.data('task-url') || '',
                    projectName: $el.data('project-name') || '',
                    keystrokes: $el.data('keystrokes') || 0,
                    clicks: $el.data('clicks') || 0,
                    scrolls: $el.data('scrolls') || 0,
                    previousCapturedAt: $el.data('previous-captured-at') || '',
                    nextCapturedAt: $el.data('next-captured-at') || '',
                    isAttention: String($el.data('is-attention') || '0') === '1',
                    isIdle: String($el.data('is-idle') || '0') === '1',
                    searchHaystack: ($el.data('search-haystack') || '').toString(),
                };
            }

            function renderRelated(current, allCards) {
                const $related = $('[data-drawer-related]');
                const relatedItems = allCards
                    .map(collectCardData)
                    .filter(function (item) {
                        return item.imageUrl
                            && item.imageUrl !== current.imageUrl
                            && (
                                (current.projectName && item.projectName === current.projectName)
                                || (current.activeApp && item.activeApp === current.activeApp)
                            );
                    })
                    .slice(0, 3);

                if (!relatedItems.length) {
                    $related.html('<div class="col-sm-12"><div class="bg-grey rounded p-20 text-center f-12 text-lightest">No related screenshots.</div></div>');
                    return;
                }

                $related.html(relatedItems.map(function (item) {
                    return '<div class="col-sm-4 mb-2">' +
                        '<button type="button" class="btn btn-block p-0 border-grey rounded overflow-hidden" data-related-preview-url="' + item.imageUrl + '">' +
                        '<img src="' + item.thumbnailUrl + '" alt="" class="w-100" style="height:80px;object-fit:cover;object-position:top;">' +
                        '</button>' +
                        '</div>';
                }).join(''));
            }

            function renderDrawer(current, allCards) {
                const visibleCards = allCards.map(collectCardData);
                const currentIndex = visibleCards.findIndex(function (item) {
                    return item.el === current.el;
                });
                const previous = currentIndex >= 0 ? visibleCards[currentIndex + 1] : null;
                const next = currentIndex > 0 ? visibleCards[currentIndex - 1] : null;

                $('[data-drawer-title]').text((current.activeApp || 'Screenshot') + ' · ' + current.capturedAt);
                $('[data-drawer-image]').attr('src', current.imageUrl).attr('alt', current.windowTitle || current.activeApp || 'Screenshot');
                $('[data-drawer-time]').text(current.capturedAt);
                $('[data-drawer-app]').text(current.activeApp);
                $('[data-drawer-productivity]').text(current.productivityLabel);
                $('[data-drawer-keystrokes]').text(Number(current.keystrokes || 0).toLocaleString());
                $('[data-drawer-clicks]').text(Number(current.clicks || 0).toLocaleString());
                $('[data-drawer-scrolls]').text(Number(current.scrolls || 0).toLocaleString());
                $('[data-drawer-project]').text(current.projectName || 'No project linked');
                $('[data-drawer-window-title]').text(current.windowTitle || 'No window title available');
                $('[data-drawer-task]').text(current.taskHeading || 'No task linked');

                $('[data-drawer-category]')
                    .text(current.productivityLabel)
                    .removeClass('badge-success badge-danger badge-warning badge-secondary')
                    .addClass(current.category === 'productive' ? 'badge-success' : (current.category === 'unproductive' ? 'badge-danger' : 'badge-warning'));

                const $taskStatus = $('[data-drawer-task-status]');
                const $taskPriority = $('[data-drawer-task-priority]');
                const $taskDueDate = $('[data-drawer-task-due-date]');

                $taskStatus.toggleClass('d-none', !current.taskStatus).text(current.taskStatus ? ('Status: ' + current.taskStatus) : '');
                $taskPriority.toggleClass('d-none', !current.taskPriority).text(current.taskPriority ? ('Priority: ' + current.taskPriority) : '');
                $taskDueDate.toggleClass('d-none', !current.taskDueDate).text(current.taskDueDate ? ('Due: ' + current.taskDueDate) : '');

                $('[data-drawer-previous]').text(previous ? previous.capturedAt : 'No previous screenshot');
                $('[data-drawer-next]').text(next ? next.capturedAt : 'No next screenshot');

                const previewParams = new URLSearchParams({
                    image_url: current.imageUrl,
                    active_app: current.activeApp,
                    window_title: current.windowTitle,
                    captured_at: current.capturedAt,
                });

                if (current.taskHeading) {
                    previewParams.set('task_heading', current.taskHeading);
                    previewParams.set('task_project', current.taskProject || '');
                    previewParams.set('task_status', current.taskStatus || '');
                    previewParams.set('task_priority', current.taskPriority || '');
                    previewParams.set('task_due_date', current.taskDueDate || '');
                    previewParams.set('task_url', current.taskUrl || '');
                }

                $('[data-drawer-open-preview]').off('click').on('click', function () {
                    $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_XL, previewBaseUrl + '?' + previewParams.toString());
                });

                renderRelated(current, allCards);
                activeCard = current.el;
                openDrawer();
            }

            function applyFilters() {
                const filters = {
                    app: ($('#screenshot-app-filter').val() || '').toString().toLowerCase(),
                    project: ($('#screenshot-project-filter').val() || '').toString().toLowerCase(),
                    category: ($('#screenshot-category-filter').val() || '').toString(),
                    productivity: ($('#screenshot-productivity-filter').val() || '').toString(),
                    search: ($('#screenshot-search').val() || '').toString().toLowerCase(),
                    attentionOnly: $('#screenshot-attention-only').is(':checked'),
                    idleOnly: $('#screenshot-idle-only').is(':checked'),
                };

                $cards.each(function () {
                    const $card = $(this);
                    const data = collectCardData(this);
                    const matchesApp = !filters.app || (data.activeApp || '').toLowerCase().includes(filters.app);
                    const matchesProject = !filters.project || (data.projectName || '').toLowerCase().includes(filters.project);
                    const matchesCategory = !filters.category || data.category === filters.category;
                    const matchesProductivity = !filters.productivity || (
                        filters.productivity === 'attention'
                            ? data.isAttention
                            : data.category === filters.productivity
                    );
                    const matchesSearch = !filters.search || data.searchHaystack.includes(filters.search);
                    const matchesAttention = !filters.attentionOnly || data.isAttention;
                    const matchesIdle = !filters.idleOnly || data.isIdle;

                    const visible = matchesApp && matchesProject && matchesCategory && matchesProductivity && matchesSearch && matchesAttention && matchesIdle;
                    const $col = $card.closest('[class*="col-"]');

                    if ($col.length) {
                        $col.toggleClass('d-none', !visible);
                    } else {
                        $card.toggleClass('d-none', !visible);
                    }
                });
            }

            $('#apply-screenshot-task-filter').on('click', function () {
                const task = $('#screenshot-task-filter').val() || 'all';
                window.location.href = buildScreenshotTabUrl(task);
            });

            $('#clear-screenshot-task-filter').on('click', function () {
                window.location.href = buildScreenshotTabUrl('all');
            });

            $('#screenshot-app-filter, #screenshot-project-filter, #screenshot-category-filter, #screenshot-productivity-filter, #screenshot-search, #screenshot-attention-only, #screenshot-idle-only')
                .on('input change', applyFilters);

            $('body').off('click.monitorScreenshot').on('click.monitorScreenshot', '.js-screenshot-card', function (e) {
                e.preventDefault();
                const visibleCards = getVisibleCards();
                const current = collectCardData(this);
                renderDrawer(current, visibleCards.length ? visibleCards : $cards.toArray());
            });

            $('body').off('click.monitorScreenshotRelated').on('click.monitorScreenshotRelated', '[data-related-preview-url]', function (e) {
                e.preventDefault();
                const url = $(this).data('related-preview-url');
                if (url) {
                    window.open(url, '_blank', 'noopener,noreferrer');
                }
            });

            $('body').off('click.monitorScreenshotDrawerClose').on('click.monitorScreenshotDrawerClose', '[data-screenshot-drawer-close]', closeDrawer);

            $(document).on('keydown.monitorScreenshotDrawer', function (e) {
                if (e.key === 'Escape' && !$drawer.hasClass('d-none')) {
                    closeDrawer();
                }
            });

            applyFilters();
        })();
    </script>
@endpush
