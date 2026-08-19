@php
    $visibleApps = collect($topApps ?? [])->take(5);
    $count = $visibleApps->count();
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Time Allocation Breakdown</h4>
                <p class="f-12 text-lightest mb-0 mt-1">Top applications by time spent today, with category context.</p>
            </div>
            <a href="{{ $appLink }}" class="btn btn-primary btn-sm rounded f-12">
                View All Applications
                <i class="fa fa-arrow-right ml-1" aria-hidden="true"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-20">
        @forelse ($visibleApps as $app)
            @php
                $badgeClass = match ($app['category'] ?? 'neutral') {
                    'productive' => 'badge-success',
                    'unproductive' => 'badge-danger',
                    default => 'badge-secondary',
                };
            @endphp
            <div class="mb-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1 mr-3">
                        @include('monitor::analytics.partials.app-icon', [
                            'size' => 36,
                            'iconUrl' => $app['icon_url'] ?? null,
                            'letterAvatar' => $app['letter_avatar'] ?? null,
                            'alt' => $app['app_name'],
                        ])
                        <div class="ml-3 flex-grow-1">
                            <p class="text-truncate f-14 f-w-500 text-darkest-grey mb-1">{{ $app['app_name'] }}</p>
                            <span class="badge {{ $badgeClass }} mr-2">{{ ucfirst($app['category'] ?? 'neutral') }}</span>
                            <span class="f-12 text-lightest">{{ ucfirst($app['category'] ?? 'neutral') }} work</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="f-14 f-w-500 text-darkest-grey">{{ $app['label'] }}</div>
                        <div class="f-12 text-lightest">{{ number_format($app['bar_pct'] ?? 0, 1) }}% of day</div>
                    </div>
                </div>
                <div class="progress mt-2" style="height:8px;">
                    <div class="progress-bar progress-bar-primary" style="width: {{ $app['bar_pct'] ?? 0 }}%"></div>
                </div>
            </div>
        @empty
            <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No application usage data available.</p>
        @endforelse
        <div class="f-12 text-lightest mt-2">Top {{ $count }} applications shown first.</div>
    </div>
</div>
