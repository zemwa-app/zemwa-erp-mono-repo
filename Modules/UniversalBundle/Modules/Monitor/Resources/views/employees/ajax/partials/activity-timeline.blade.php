@php
    $items = collect($timeline ?? []);
    $maxDuration = max((int) $items->max('duration_seconds'), 1);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Application Timeline</h4>
        <p class="f-12 text-lightest mb-0 mt-1">A visual view of how the workday moved from app to app.</p>
    </div>
    <div class="card-body p-20">
        @forelse ($items as $item)
            @php
                $tone = $item['category'] ?? $item['website_type'] ?? null;
                $barClass = $tone === 'productive' ? 'progress-bar-success' : ($tone === 'unproductive' ? 'progress-bar-danger' : 'progress-bar-primary');
            @endphp
            <div class="d-flex align-items-center mb-3">
                <div class="f-12 f-w-500 text-darkest-grey text-right mr-3" style="width:64px;">{{ $item['time'] }}</div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="f-14 f-w-500 text-darkest-grey mr-2">{{ $item['app_name'] ?? $item['domain'] ?? 'Unknown' }}</span>
                        @if (!empty($item['duration_label']))
                            <span class="badge badge-secondary">{{ $item['duration_label'] }}</span>
                        @endif
                    </div>
                    <div class="progress mt-2" style="height:10px;">
                        <div class="progress-bar {{ $barClass }}" style="width: {{ max(12, min(100, (int) round((($item['duration_seconds'] ?? 0) / $maxDuration) * 100))) }}%"></div>
                    </div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No timeline available.</p>
        @endforelse
    </div>
</div>
