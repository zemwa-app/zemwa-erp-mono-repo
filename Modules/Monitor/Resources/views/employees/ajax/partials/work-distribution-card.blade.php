<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Work Distribution</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Where the day went across work categories.</p>
    </div>
    <div class="card-body p-20">
        @forelse ($workDistribution as $item)
            @php
                $barClass = match ($item['label']) {
                    'Development' => 'progress-bar-primary',
                    'Communication' => 'progress-bar-success',
                    'Documentation' => 'progress-bar-info',
                    'Research' => 'progress-bar-warning',
                    default => 'progress-bar-default',
                };
            @endphp
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between f-14 mb-2">
                    <span class="f-w-500 text-darkest-grey">{{ $item['label'] }}</span>
                    <span class="f-12 text-lightest">{{ $item['label_seconds'] }} · {{ number_format($item['pct'], 1) }}%</span>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar {{ $barClass }}" style="width: {{ $item['pct'] }}%"></div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No work distribution available.</p>
        @endforelse
    </div>
</div>
