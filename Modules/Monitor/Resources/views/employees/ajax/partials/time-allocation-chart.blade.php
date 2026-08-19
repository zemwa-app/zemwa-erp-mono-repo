@php
    $bars = collect($timeAllocation ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Time Allocation</h4>
        <p class="f-12 text-lightest mb-0 mt-1">The top applications by share of active app time.</p>
    </div>
    <div class="card-body p-20">
        @forelse ($bars as $bar)
            @php
                $barClass = match ($bar['category'] ?? 'neutral') {
                    'productive' => 'progress-bar-success',
                    'unproductive' => 'progress-bar-danger',
                    default => 'progress-bar-primary',
                };
            @endphp
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="flex-grow-1 mr-3">
                        <p class="text-truncate f-14 f-w-500 text-darkest-grey mb-0">{{ $bar['app_name'] }}</p>
                        <p class="f-12 text-lightest mb-0">{{ $bar['trend_label'] }}</p>
                    </div>
                    <div class="text-right">
                        <div class="f-14 f-w-500 text-darkest-grey">{{ number_format((float) ($bar['bar_pct'] ?? 0), 1) }}%</div>
                        <div class="f-12 text-lightest">{{ $bar['duration_label'] }}</div>
                    </div>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar {{ $barClass }}" style="width: {{ $bar['bar_pct'] ?? 0 }}%"></div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No application time available.</p>
        @endforelse
        <div class="f-12 text-lightest">Active application time for the day: {{ $activeTimeLabel ?? '0m' }}</div>
    </div>
</div>
