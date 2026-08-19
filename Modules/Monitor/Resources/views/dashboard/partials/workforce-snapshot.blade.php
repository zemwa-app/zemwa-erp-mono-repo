@php
    $meta = $dashboard['meta'] ?? [];
    $currentActivity = $dashboard['current_activity'] ?? [];
    $departments = array_slice($dashboard['department_stats'] ?? [], 0, 3);
    $applicationUsage = $dashboard['application_usage'] ?? [];

    $toneBarClass = [
        'green' => 'progress-bar-success',
        'orange' => 'progress-bar-warning',
        'amber' => 'progress-bar-warning',
        'gray' => 'progress-bar-secondary',
    ];
@endphp

<div>
    <x-cards.data class="mb-3" title="Live Workforce Snapshot">
        <p class="f-12 text-lightest mb-3">Immediate view of the team state right now</p>

        <div class="row">
            @foreach ($currentActivity as $item)
                @php
                    $tone = $item['tone'] ?? 'gray';
                    $barClass = $toneBarClass[$tone] ?? $toneBarClass['gray'];
                @endphp
                <div class="col-6 mb-3">
                    <div class="card bg-additional-grey border-0 p-3 h-100">
                        <p class="f-11 text-lightest text-uppercase mb-2">{{ $item['label'] }}</p>
                        <p class="f-21 font-weight-bold text-darkest-grey mb-2">{{ number_format((int) ($item['value'] ?? 0)) }}</p>
                        <div class="progress" style="height: 6px; margin-bottom: 0;">
                            <div class="progress-bar {{ $barClass }}" role="progressbar"
                                style="width: {{ max(0, min(100, (float) ($item['pct'] ?? 0))) }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card bg-additional-grey border-0 p-3 mt-2">
            <div class="d-flex justify-content-between align-items-center">
                <p class="f-12 f-w-500 text-darkest-grey mb-0">Focus time today</p>
                <p class="f-14 font-weight-bold text-darkest-grey mb-0">{{ $meta['focus_time_label'] ?? '0m' }}</p>
            </div>
            <p class="f-12 text-lightest mb-0 mt-1">Average per employee: {{ $meta['average_focus_label'] ?? '0m' }}</p>
        </div>
    </x-cards.data>

    @include('monitor::dashboard.partials.department-health-widget', [
        'headline' => 'Department Status',
        'subtitle' => 'Compact live snapshot by department',
        'departments' => $departments,
        'compact' => true,
    ])

    @if (!empty($applicationUsage))
        <x-cards.data class="mb-0 mt-3" title="Top Applications">
            <p class="f-12 text-lightest mb-3">Most used applications right now</p>
            @foreach ($applicationUsage as $app)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="f-14 text-darkest-grey text-truncate">{{ $app['label'] }}</span>
                        <span class="f-12 text-lightest ml-2">{{ number_format((float) ($app['pct'] ?? 0), 1) }}%</span>
                    </div>
                    <div class="progress mt-2" style="height: 8px; margin-bottom: 0;">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: {{ max(0, min(100, (float) ($app['pct'] ?? 0))) }}%"></div>
                    </div>
                </div>
            @endforeach
        </x-cards.data>
    @endif
</div>
