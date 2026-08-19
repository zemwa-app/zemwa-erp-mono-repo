@php
    $metrics = [
        ['label' => 'Total Active Apps', 'value' => number_format((int) ($summary['app_count'] ?? 0)), 'meta' => 'Apps with activity today'],
        ['label' => 'Total Application Time', 'value' => $summary['total_duration_label'] ?? '0m', 'meta' => 'Apps only'],
        ['label' => 'Productive Time', 'value' => $summary['productive_label'] ?? '0m', 'meta' => number_format((float) ($summary['productive_pct'] ?? 0), 1) . '% of app time'],
        ['label' => 'Neutral Time', 'value' => $summary['neutral_label'] ?? '0m', 'meta' => number_format((float) ($summary['neutral_pct'] ?? 0), 1) . '% of app time'],
        ['label' => 'Unproductive Time', 'value' => $summary['unproductive_label'] ?? '0m', 'meta' => number_format((float) ($summary['unproductive_pct'] ?? 0), 1) . '% of app time'],
    ];
    $mostUsedApp = $summary['most_used_app'] ?? '—';
    $mostUsedTime = $summary['most_used_app_time_label'] ?? '0m';
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Application Usage Summary</h4>
                <p class="f-12 text-lightest mb-0 mt-1">A manager-level view of where application time went today.</p>
            </div>
            <span class="badge badge-secondary">{{ number_format((int) ($summary['app_count'] ?? 0)) }} active apps</span>
        </div>
    </div>
    <div class="card-body p-20">
        <div class="row">
            @foreach ($metrics as $metric)
                <div class="col-md-6 col-lg-4 col-xl mb-3">
                    <div class="bg-grey rounded p-3 h-100">
                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                        <div class="f-16 f-w-500 text-darkest-grey mt-2">{{ $metric['value'] }}</div>
                        <div class="f-12 text-lightest mt-1">{{ $metric['meta'] }}</div>
                    </div>
                </div>
            @endforeach
            <div class="col-md-6 col-lg-4 col-xl mb-3">
                <div class="bg-dark rounded p-20 text-white h-100">
                    <div class="f-11 text-uppercase text-lightest">Most Used App</div>
                    <div class="d-flex align-items-start mt-3">
                        @include('monitor::analytics.partials.app-icon', [
                            'size' => 36,
                            'iconUrl' => $summary['most_used_app_icon'] ?? null,
                            'letterAvatar' => $summary['most_used_app_letter_avatar'] ?? null,
                            'alt' => $mostUsedApp,
                        ])
                        <div class="ml-3 flex-grow-1">
                            <div class="text-truncate f-16 f-w-500">{{ $mostUsedApp }}</div>
                            <div class="f-14 text-lightest mt-1">{{ $mostUsedTime }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
