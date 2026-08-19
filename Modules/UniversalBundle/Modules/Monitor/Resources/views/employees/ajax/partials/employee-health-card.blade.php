@php
    $activeLabel = $overview['active_label'] ?? '0m';
    $idleLabel = $overview['idle_label'] ?? '0m';
    $focusLabel = $overview['focus_label'] ?? '0m';
    $screenshots = $overview['screenshot_count'] ?? 0;
    $teamAverage = (float) ($overview['team_average_score'] ?? 0);
    $scoreDelta = (float) ($overview['score_delta'] ?? 0);
    $scoreBadge = match ($productivityStatusTone ?? 'gray') {
        'green', 'emerald' => 'badge-success',
        'orange', 'amber' => 'badge-warning',
        'red' => 'badge-danger',
        default => 'badge-secondary',
    };
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Employee Health Summary</h4>
                <p class="f-12 text-lightest mb-0 mt-1">A quick read on whether this employee looks healthy, focused, and productive today.</p>
            </div>
            <span class="badge {{ $scoreBadge }}">{{ $productivityStatusLabel }}</span>
        </div>
    </div>
    <div class="card-body p-20">
        <div class="row">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div class="d-flex align-items-start">
                    <div class="monitor-health-score-ring mr-3" style="--score: {{ max(0, min(100, $score)) }};">
                        <div class="monitor-health-score-ring__inner">
                            <div>
                                <div class="f-16 f-w-500 text-darkest-grey">{{ number_format($score, 1) }}</div>
                                <div class="f-11 text-lightest text-uppercase">Score</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="f-14 f-w-500 text-darkest-grey mr-2">{{ number_format($score, 1) }}/100</span>
                            <span class="badge badge-secondary">
                                {{ $teamAverage > 0 ? number_format($scoreDelta, 1) : '—' }} vs team average
                            </span>
                        </div>
                        <p class="f-14 text-dark-grey mt-2 mb-3">
                            Productivity is the main signal here, with active time, idle time, focus time, and screenshots captured shown as supporting context.
                        </p>
                        <div class="row">
                            @foreach ([
                                ['label' => 'Active Time', 'value' => $activeLabel],
                                ['label' => 'Idle Time', 'value' => $idleLabel],
                                ['label' => 'Focus Time', 'value' => $focusLabel],
                                ['label' => 'Screenshots', 'value' => number_format($screenshots)],
                            ] as $metric)
                                <div class="col-sm-6 mb-3">
                                    <div class="bg-grey rounded p-3">
                                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                                        <div class="f-14 f-w-500 text-darkest-grey mt-1">{{ $metric['value'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-grey rounded p-20 mb-3">
                    <div class="f-11 text-lightest text-uppercase">Current Read</div>
                    <div class="d-flex align-items-end mt-2">
                        <div class="f-21 f-w-500 text-darkest-grey">{{ number_format($score, 1) }}</div>
                        <div class="f-14 text-lightest ml-2 mb-1">/100</div>
                    </div>
                    <div class="progress mt-3" style="height:8px;">
                        <div class="progress-bar progress-bar-primary" style="width: {{ max(0, min(100, $score)) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between f-12 text-lightest mt-2">
                        <span>Healthy today</span>
                        <span>{{ $productivityStatusLabel }}</span>
                    </div>
                </div>
                <div class="border-grey rounded p-20 bg-white">
                    <div class="f-11 text-lightest text-uppercase">Context</div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between f-14 mb-2">
                            <span class="text-lightest">Team average</span>
                            <span class="f-w-500 text-darkest-grey">{{ number_format($teamAverage, 1) }}%</span>
                        </div>
                        <div class="d-flex justify-content-between f-14">
                            <span class="text-lightest">Comparison</span>
                            <span class="f-w-500 {{ $scoreDelta >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $scoreDelta >= 0 ? '+' : '' }}{{ number_format($scoreDelta, 1) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
