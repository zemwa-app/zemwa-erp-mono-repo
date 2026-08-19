@php
    $tone = $tone ?? 'amber';
    $issue = $issue ?? ($reason ?? 'Attention required');
    $name = $name ?? 'Unknown employee';
    $department = $department ?? 'Unassigned';
    $avatarUrl = $avatar_url ?? null;
@endphp

<div class="card bg-white border-0 b-shadow-4 p-20">
    <div class="d-flex">
        <div class="mr-3">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $name }}" class="taskEmployeeImg rounded" style="width: 44px; height: 44px;">
            @else
                <div class="taskEmployeeImg rounded d-flex align-items-center justify-content-center bg-additional-grey f-14 font-weight-bold text-dark-grey"
                    style="width: 44px; height: 44px;">
                    {{ strtoupper(mb_substr($name, 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="w-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="mr-3">
                    <p class="mb-0 f-14 f-w-500 text-darkest-grey text-truncate">{{ $name }}</p>
                    <p class="mb-0 f-12 text-lightest">{{ $department }} · {{ $employee_code ?? '—' }}</p>
                </div>
                @include('monitor::dashboard.partials.severity-badge', [
                    'tone' => $tone,
                    'label' => $severity_label ?? ucfirst($tone),
                ])
            </div>

            <p class="f-14 f-w-500 text-darkest-grey mt-3 mb-0">{{ $issue }}</p>

            <div class="mt-3">
                <span class="badge badge-secondary f-11 mr-1 mb-1">
                    {{ number_format((float) ($score ?? 0), 1) }}% productivity
                </span>
                <span class="badge badge-secondary f-11 mr-1 mb-1">
                    {{ $last_activity_label ?? 'No recent activity' }}
                </span>
                <span class="badge badge-secondary f-11 mb-1">
                    {{ $active_app ?? 'No active app' }}
                </span>
            </div>

            <div class="text-right mt-3">
                <a href="{{ $view_url ?? '#' }}" class="btn btn-primary btn-sm">
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>
