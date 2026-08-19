@php
    $items = collect($apps ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Most Important Applications</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Top applications ranked by time spent and trend versus normal.</p>
    </div>
    <div class="card-body p-20">
        <div class="row">
            @forelse ($items as $app)
                @php
                    $badgeClass = match ($app['category'] ?? 'neutral') {
                        'productive' => 'badge-success',
                        'unproductive' => 'badge-danger',
                        default => 'badge-secondary',
                    };
                    $trendClass = match ($app['trend_vs_average_tone'] ?? 'gray') {
                        'green' => 'text-success',
                        'amber', 'orange' => 'text-warning',
                        default => 'text-lightest',
                    };
                @endphp
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="bg-grey rounded p-20 h-100">
                        <div class="d-flex align-items-start">
                            @include('monitor::analytics.partials.app-icon', [
                                'size' => 36,
                                'iconUrl' => $app['icon_url'] ?? null,
                                'letterAvatar' => $app['letter_avatar'] ?? null,
                                'alt' => $app['app_name'] ?? '',
                            ])
                            <div class="ml-3 flex-grow-1">
                                <p class="text-truncate f-14 f-w-500 text-darkest-grey mb-1">{{ $app['app_name'] }}</p>
                                <span class="badge {{ $badgeClass }} mr-2">{{ \Modules\Monitor\Services\MonitorEmployeeDetailService::categoryLabel($app['category'] ?? null) }}</span>
                                <span class="f-12 {{ $trendClass }}">{{ $app['trend_vs_average_label'] ?? 'Within normal range' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-3">
                            <div>
                                <div class="f-16 f-w-500 text-darkest-grey">{{ $app['duration_label'] ?? '0m' }}</div>
                                <div class="f-12 text-lightest">{{ number_format((float) ($app['day_share_pct'] ?? 0), 1) }}% of day</div>
                            </div>
                            <div class="text-right f-12 text-lightest">
                                <div>Sessions: {{ number_format((int) ($app['session_count'] ?? 0)) }}</div>
                                <div>{{ $app['process_names_label'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height:8px;">
                            <div class="progress-bar progress-bar-primary" style="width: {{ $app['day_share_pct'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No application cards available.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
