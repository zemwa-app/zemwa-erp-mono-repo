@php
    $score = (int) ($summary['application_health_score'] ?? 0);
    $label = $summary['application_health_label'] ?? 'Good';
    $tone = $summary['application_health_tone'] ?? 'gray';
    $badgeClass = match ($tone) {
        'green', 'emerald' => 'badge-success',
        'amber', 'orange' => 'badge-warning',
        'red' => 'badge-danger',
        default => 'badge-secondary',
    };
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Application Health</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Sticky desktop snapshot for quick manager review.</p>
    </div>
    <div class="card-body p-20">
        <div class="bg-grey rounded p-20 mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="f-11 text-lightest text-uppercase">Health Score</div>
                    <div class="f-21 f-w-500 text-darkest-grey mt-2">{{ number_format($score) }}/100</div>
                </div>
                <span class="badge {{ $badgeClass }}">{{ $label }}</span>
            </div>
            <div class="progress mt-3" style="height:8px;">
                <div class="progress-bar progress-bar-primary" style="width: {{ max(0, min(100, $score)) }}%"></div>
            </div>
        </div>

        <div class="row">
            @foreach ([
                ['label' => 'Current Application', 'value' => $summary['current_app'] ?? 'No active application', 'sub' => $summary['current_activity_label'] ?? 'No activity recorded yet'],
                ['label' => 'Current Session', 'value' => $summary['current_session_duration_label'] ?? '0m', 'sub' => $summary['current_status_label'] ?? 'Offline'],
                ['label' => 'Top App Today', 'value' => $summary['most_used_app'] ?? '—', 'sub' => $summary['most_used_app_time_label'] ?? '0m'],
                ['label' => 'Productive Time', 'value' => $summary['productive_label'] ?? '0m', 'sub' => number_format((float) ($summary['productive_pct'] ?? 0), 1) . '% of app time'],
            ] as $metric)
                <div class="col-6 mb-3">
                    <div class="bg-grey rounded p-3">
                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                        <div class="f-14 f-w-500 text-darkest-grey mt-1">{{ $metric['value'] }}</div>
                        <div class="f-12 text-lightest mt-1">{{ $metric['sub'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
