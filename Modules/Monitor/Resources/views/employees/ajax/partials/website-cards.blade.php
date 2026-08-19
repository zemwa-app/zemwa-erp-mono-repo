@php
    $items = collect($websiteRows ?? []);
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Top Websites</h4>
        <p class="f-12 text-lightest mb-0 mt-1">The websites that dominated the day.</p>
    </div>
    <div class="card-body p-20">
        <div class="row">
            @forelse ($items as $site)
                @php
                    $typeBadge = match ($site['website_type'] ?? 'Other') {
                        'Development', 'AI Tools', 'Documentation' => 'badge-success',
                        'Research', 'News' => 'badge-info',
                        'Communication' => 'badge-warning',
                        'Entertainment', 'Social Media', 'Shopping' => 'badge-danger',
                        default => 'badge-secondary',
                    };
                    $productivityClass = match ($site['category'] ?? 'neutral') {
                        'productive' => 'text-success',
                        'unproductive' => 'text-danger',
                        default => 'text-warning',
                    };
                @endphp
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="bg-grey rounded p-20 h-100">
                        <div class="d-flex align-items-start">
                            @include('monitor::analytics.partials.app-icon', [
                                'size' => 36,
                                'iconUrl' => $site['icon_url'] ?? null,
                                'letterAvatar' => $site['letter_avatar'] ?? null,
                                'alt' => $site['display_name'] ?? '',
                            ])
                            <div class="ml-3 flex-grow-1">
                                <p class="text-truncate f-14 f-w-500 text-darkest-grey mb-1">{{ $site['display_name'] ?? 'Unknown' }}</p>
                                <span class="badge {{ $typeBadge }} mr-2">{{ $site['website_type'] ?? 'Other' }}</span>
                                <span class="f-12 {{ $productivityClass }}">{{ \Modules\Monitor\Services\MonitorEmployeeDetailService::categoryLabel($site['category'] ?? null) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-3">
                            <div>
                                <div class="f-16 f-w-500 text-darkest-grey">{{ $site['duration_label'] ?? '0m' }}</div>
                                <div class="f-12 text-lightest">{{ number_format((int) ($site['visit_count'] ?? 0)) }} visits</div>
                            </div>
                            <div class="text-right f-12 text-lightest">
                                <div>{{ number_format((float) ($site['bar_pct'] ?? 0), 1) }}% of day</div>
                                <div>{{ number_format((int) ($site['unique_pages'] ?? 0)) }} unique pages</div>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height:8px;">
                            <div class="progress-bar progress-bar-primary" style="width: {{ $site['bar_pct'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="bg-grey rounded p-20 text-center f-14 text-lightest mb-0">No website cards available.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
