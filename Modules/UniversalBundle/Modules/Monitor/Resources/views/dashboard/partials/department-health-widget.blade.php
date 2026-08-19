@php
    $departments = $departments ?? [];
    $headline = $headline ?? 'Department Status';
    $subtitle = $subtitle ?? 'Online versus offline balance by department';
    $compact = (bool) ($compact ?? false);
@endphp

<x-cards.data class="{{ $compact ? 'mb-3' : 'mb-0' }}" :title="$headline">
    <p class="f-12 text-lightest mb-3">{{ $subtitle }}</p>

    @forelse ($departments as $department)
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mr-3">
                    <p class="mb-0 {{ $compact ? 'f-12' : 'f-14' }} text-darkest-grey text-truncate">{{ $department['name'] ?? 'Unassigned' }}</p>
                    <p class="mb-0 f-12 text-lightest">
                        {{ $department['online'] ?? 0 }} online · {{ $department['offline'] ?? 0 }} offline
                    </p>
                </div>
                <div class="text-right">
                    <p class="mb-0 {{ $compact ? 'f-12' : 'f-14' }} font-weight-bold text-darkest-grey">{{ number_format((float) ($department['online_pct'] ?? 0), 1) }}%</p>
                    <p class="mb-0 f-11 text-lightest">online</p>
                </div>
            </div>

            <div class="progress mt-2" style="height: 8px; margin-bottom: 0;">
                <div class="progress-bar progress-bar-success" role="progressbar"
                    style="width: {{ max(0, min(100, (float) ($department['online_pct'] ?? 0))) }}%"></div>
            </div>

            <div class="d-flex justify-content-between f-11 text-lightest mt-2">
                <span>{{ $department['total'] ?? 0 }} employees</span>
                <span>{{ number_format((float) ($department['score'] ?? 0), 1) }}% avg productivity</span>
            </div>
        </div>
    @empty
        <p class="text-center f-14 text-lightest py-4 mb-0">@lang('messages.noRecordFound')</p>
    @endforelse
</x-cards.data>
