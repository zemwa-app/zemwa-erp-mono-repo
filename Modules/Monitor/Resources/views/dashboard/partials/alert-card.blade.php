@php
    $tone = $tone ?? 'amber';
    $status = $status ?? 'offline';
    $reason = $reason ?? 'Attention required';

    $borderClasses = [
        'red' => 'border-danger',
        'orange' => 'border-warning',
        'amber' => 'border-warning',
        'gray' => 'border-additional-grey',
    ];
@endphp

<div class="card bg-white border {{ $borderClasses[$tone] ?? $borderClasses['gray'] }} b-shadow-4 p-20">
    <div class="d-flex">
        <div class="taskEmployeeImg rounded d-flex align-items-center justify-content-center bg-white f-14 font-weight-bold text-darkest-grey mr-3"
            style="width: 40px; height: 40px; flex-shrink: 0;">
            {{ strtoupper(mb_substr($name ?? 'A', 0, 1)) }}
        </div>
        <div class="w-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="mr-3">
                    <p class="mb-0 f-14 f-w-500 text-darkest-grey text-truncate">{{ $name ?? 'Unknown employee' }}</p>
                    <p class="mb-0 f-12 text-lightest">{{ $employee_code ?? '—' }} · {{ $department ?? 'Unassigned' }}</p>
                </div>
                @include('monitor::dashboard.partials.status-badge', [
                    'status' => $status,
                    'label' => ucfirst($status),
                    'compact' => true,
                ])
            </div>

            <div class="mt-3">
                <span class="badge badge-secondary f-11 mr-1 mb-1">{{ $reason }}</span>
                <span class="badge badge-secondary f-11 mr-1 mb-1">{{ $active_app ?? 'No active app' }}</span>
                <span class="badge badge-secondary f-11 mb-1">{{ $last_activity_label ?? 'No recent activity' }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="f-12 text-lightest">
                    <span class="font-weight-bold text-darkest-grey">{{ number_format((float) ($score ?? 0), 1) }}%</span>
                    productivity score
                </div>
                <a href="{{ $view_url ?? '#' }}" class="btn btn-primary btn-sm">
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>
