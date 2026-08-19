@php
    $items = collect($intentBuckets ?? []);
    $toneMap = [
        'gray' => 'progress-bar-primary',
        'emerald' => 'progress-bar-success',
        'sky' => 'progress-bar-info',
        'amber' => 'progress-bar-warning',
        'red' => 'progress-bar-danger',
    ];
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Work Intent Analysis</h4>
        <p class="f-12 text-lightest mb-0 mt-1">How browsing time appears to have been used.</p>
    </div>
    <div class="card-body p-20">
        @forelse ($items as $item)
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="f-14 f-w-500 text-darkest-grey">{{ $item['label'] }}</span>
                    <span class="f-12 text-lightest">{{ $item['label_seconds'] }} · {{ number_format((float) ($item['pct'] ?? 0), 1) }}%</span>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar {{ $toneMap[$item['tone']] ?? 'progress-bar-default' }}" style="width: {{ $item['pct'] ?? 0 }}%"></div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No work intent data available.</p>
        @endforelse
    </div>
</div>
