@php
    $items = collect($timeline ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Website Timeline</h4>
        <p class="f-12 text-lightest mb-0 mt-1">A quick visual of browsing flow across the day.</p>
    </div>
    <div class="card-body p-20">
        @forelse ($items as $item)
            @php
                $barClass = $item['website_type'] === 'Communication' ? 'progress-bar-warning' : ($item['website_type'] === 'Entertainment' ? 'progress-bar-danger' : 'progress-bar-primary');
            @endphp
            <div class="d-flex align-items-center mb-3">
                <div class="f-12 f-w-500 text-darkest-grey text-right mr-3" style="width:64px;">{{ $item['time'] }}</div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="f-14 f-w-500 text-darkest-grey">{{ $item['domain'] }}</span>
                        <span class="f-12 text-lightest">{{ $item['duration_label'] }}</span>
                    </div>
                    <div class="progress mt-2" style="height:10px;">
                        <div class="progress-bar {{ $barClass }}" style="width: {{ max(12, min(100, (int) ($item['pct'] ?? 0))) }}%"></div>
                    </div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No website timeline available.</p>
        @endforelse
    </div>
</div>
