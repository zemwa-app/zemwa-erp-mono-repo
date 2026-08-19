@php
    $scoreBadge = match ($browsingScoreTone ?? 'gray') {
        'green', 'emerald' => 'badge-success',
        'amber', 'orange' => 'badge-warning',
        'red' => 'badge-danger',
        default => 'badge-secondary',
    };
    $primaryMetrics = [
        ['label' => 'Total Websites Visited', 'value' => number_format((int) $websiteCount), 'meta' => 'Unique domains today'],
        ['label' => 'Total Browsing Time', 'value' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) $totalBrowsingSeconds), 'meta' => 'All website activity'],
        ['label' => 'Productive Browsing Time', 'value' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) $productiveSeconds), 'meta' => 'Helpful work activity'],
        ['label' => 'Neutral Browsing Time', 'value' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) $neutralSeconds), 'meta' => 'Neither helpful nor harmful'],
        ['label' => 'Attention Required Time', 'value' => \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) $attentionSeconds), 'meta' => 'Needs a quick review'],
    ];
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Website Activity Summary</h4>
        <p class="f-12 text-lightest mb-0 mt-1">A manager’s first read on where browsing time went today.</p>
    </div>
    <div class="card-body p-20">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    @foreach ($primaryMetrics as $metric)
                        <div class="col-sm-6 col-xl-4 mb-3">
                            <div class="bg-grey rounded p-3 h-100">
                                <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                                <div class="f-16 f-w-500 text-darkest-grey mt-2">{{ $metric['value'] }}</div>
                                <div class="f-12 text-lightest mt-1">{{ $metric['meta'] }}</div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-sm-6 col-xl-4 mb-3">
                        <div class="bg-dark rounded p-20 text-white h-100">
                            <div class="f-11 text-uppercase text-lightest">Most Visited Website</div>
                            <div class="d-flex align-items-start mt-3">
                                @include('monitor::analytics.partials.app-icon', [
                                    'size' => 36,
                                    'iconUrl' => $mostVisitedWebsite['icon_url'] ?? null,
                                    'letterAvatar' => $mostVisitedWebsite['letter_avatar'] ?? null,
                                    'alt' => $mostVisitedWebsite['display_name'] ?? 'Website',
                                ])
                                <div class="ml-3 flex-grow-1">
                                    <div class="text-truncate f-16 f-w-500">{{ $mostVisitedWebsite['display_name'] ?? '—' }}</div>
                                    <div class="f-14 text-lightest mt-1">{{ $mostVisitedWebsite['duration_label'] ?? '0m' }}</div>
                                    <span class="badge badge-secondary mt-2">{{ $mostVisitedWebsite['visit_count'] ?? 0 }} visits</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bg-grey rounded p-20 h-100">
                    <div class="f-11 text-lightest text-uppercase">Browsing Productivity Score</div>
                    <div class="f-21 font-weight-bold text-darkest-grey mt-2">{{ number_format((int) $browsingScore) }}/100</div>
                    <span class="badge {{ $scoreBadge }} mt-2">{{ $browsingScoreLabel }}</span>
                    <div class="progress mt-3" style="height:8px;">
                        <div class="progress-bar progress-bar-primary" style="width: {{ max(0, min(100, (int) $browsingScore)) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between f-12 text-lightest mt-2">
                        <span>Website health at a glance</span>
                        <span>{{ $browsingScoreLabel }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
