@php
    $statusBadge = match ($overview['status_tone'] ?? 'gray') {
        'green' => 'badge-success',
        'orange', 'amber' => 'badge-warning',
        default => 'badge-secondary',
    };
    $currentApp = $overview['current_app'] ?? null;
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Live Status</h4>
                <p class="f-12 text-lightest mb-0 mt-1">A sticky snapshot that stays visible while you scroll.</p>
            </div>
            <span class="badge {{ $statusBadge }}">{{ $overview['status_label'] ?? 'Offline' }}</span>
        </div>
    </div>
    <div class="card-body p-20">
        <div class="mb-3">
            <div class="f-11 text-lightest text-uppercase">Current Application</div>
            <div class="f-16 f-w-500 text-darkest-grey mt-1">{{ $currentApp ?: 'No active application' }}</div>
            <div class="f-12 text-lightest mt-1">{{ $overview['last_activity_label'] ?? 'No recent activity' }}</div>
        </div>

        <div class="row mb-3">
            @foreach ([
                ['label' => 'Productivity', 'value' => number_format($overview['productivity_score'] ?? 0, 1) . '%'],
                ['label' => 'Focus Time', 'value' => $overview['focus_label'] ?? '0m'],
                ['label' => 'Session', 'value' => $overview['current_session_duration_label'] ?? '0m'],
                ['label' => 'Last Seen', 'value' => $overview['last_activity_label'] ?? '—'],
            ] as $metric)
                <div class="col-6 mb-3">
                    <div class="bg-grey rounded p-3">
                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                        <div class="f-16 f-w-500 text-darkest-grey mt-1">{{ $metric['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-grey rounded p-3">
            <div class="f-11 text-lightest text-uppercase">Last Activity</div>
            <div class="f-14 f-w-500 text-darkest-grey mt-1">{{ $overview['last_activity_label'] ?? 'No activity recorded yet' }}</div>
            <div class="f-12 text-lightest mt-1">{{ $overview['status'] === 'offline' ? 'No activity recorded yet' : 'Live monitoring is active' }}</div>
        </div>
    </div>
</div>
