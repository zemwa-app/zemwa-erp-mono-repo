@php
    $items = collect($categoryBuckets ?? []);
    $barClasses = [
        'Development' => 'progress-bar-primary',
        'Research' => 'progress-bar-success',
        'Documentation' => 'progress-bar-info',
        'Communication' => 'progress-bar-warning',
        'AI Tools' => 'progress-bar-info',
        'Social Media' => 'progress-bar-danger',
        'Entertainment' => 'progress-bar-warning',
        'Shopping' => 'progress-bar-danger',
        'News' => 'progress-bar-info',
        'Other' => 'progress-bar-default',
    ];
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Website Categories</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Managers usually want categories before domains.</p>
    </div>
    <div class="card-body p-20">
        @forelse ($items as $item)
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="f-14 f-w-500 text-darkest-grey">{{ $item['label'] }}</span>
                    <span class="f-12 text-lightest">{{ $item['label_seconds'] }} · {{ number_format((float) ($item['pct'] ?? 0), 1) }}%</span>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar {{ $barClasses[$item['label']] ?? 'progress-bar-default' }}" style="width: {{ $item['pct'] ?? 0 }}%"></div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No category data available.</p>
        @endforelse
    </div>
</div>
