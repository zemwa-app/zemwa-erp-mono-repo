@php
    $status = $employee['status'] ?? 'offline';
    $statusLabels = [
        'online' => __('monitor::app.onlineNow'),
        'idle' => __('monitor::app.idle'),
        'paused' => __('monitor::app.paused'),
        'offline' => __('monitor::app.offline'),
    ];

    $score = (float) ($employee['score'] ?? 0);
    $viewUrl = route('monitor.show', $employee['user_id']);
    $avatarUrl = $employee['avatar_url'] ?? null;

    $dotColors = [
        'online' => '#22c55e',
        'idle' => '#f97316',
        'paused' => '#eab308',
        'offline' => '#6b7280',
    ];
    $dotColor = $dotColors[$status] ?? $dotColors['offline'];
@endphp

<tr>
    <td>
        <div class="d-flex align-items-center">
            <div class="position-relative mr-3">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $employee['name'] }}"
                        class="taskEmployeeImg rounded" style="width: 40px; height: 40px;">
                @else
                    <div class="taskEmployeeImg rounded d-flex align-items-center justify-content-center bg-additional-grey f-14 font-weight-bold text-dark-grey"
                        style="width: 40px; height: 40px;">
                        {{ strtoupper(mb_substr($employee['name'], 0, 1)) }}
                    </div>
                @endif
                <span class="position-absolute rounded-circle border border-white"
                    style="bottom: 0; right: 0; width: 12px; height: 12px; background-color: {{ $dotColor }};"></span>
            </div>
            <div>
                <p class="mb-0 f-14 f-w-500 text-darkest-grey text-truncate">{{ $employee['name'] }}</p>
                <p class="mb-0 f-12 text-lightest">{{ $employee['employee_code'] }}</p>
                @if (!empty($employee['department']))
                    <span class="badge badge-secondary f-11 mt-1">
                        {{ $employee['department'] }}
                    </span>
                @endif
            </div>
        </div>
    </td>
    <td>
        @include('monitor::dashboard.partials.status-badge', [
            'status' => $status,
            'label' => $statusLabels[$status] ?? ucfirst($status),
        ])
    </td>
    <td>
        <div class="d-flex align-items-center">
            @include('monitor::analytics.partials.app-icon', [
                'size' => 28,
                'letterAvatar' => $employee['active_app_icon'] ?? ['letter' => '?', 'color' => '#64748b'],
                'alt' => $employee['active_app'] ?? 'App',
            ])
            <div>
                <p class="mb-0 f-14 text-darkest-grey text-truncate">{{ $employee['active_app'] ?: '—' }}</p>
                <p class="mb-0 f-11 text-lightest">{{ $employee['last_activity_label'] ?? 'No recent activity' }}</p>
            </div>
        </div>
    </td>
    <td>
        @include('monitor::dashboard.partials.productivity-bar', [
            'score' => $score,
            'showScore' => true,
        ])
    </td>
    <td class="f-14 text-dark-grey">
        {{ $employee['last_activity_label'] ?? 'No recent activity' }}
    </td>
    <td class="text-right">
        <a href="{{ $viewUrl }}" class="btn btn-primary btn-sm">
            @lang('app.view')
        </a>
    </td>
</tr>
