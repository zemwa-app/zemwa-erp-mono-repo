@php
    $badgeClass = match ($websiteHealthTone ?? 'gray') {
        'green', 'emerald' => 'badge-success',
        'amber', 'orange' => 'badge-warning',
        'red' => 'badge-danger',
        default => 'badge-secondary',
    };
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">Website Health Panel</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Sticky desktop snapshot for quick review.</p>
    </div>
    <div class="card-body p-20">
        <div class="bg-grey rounded p-20 mb-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="f-11 text-lightest text-uppercase">Browsing Productivity Score</div>
                    <div class="f-21 f-w-500 text-darkest-grey mt-2">{{ number_format((int) $browsingScore) }}/100</div>
                </div>
                <span class="badge {{ $badgeClass }}">{{ $browsingScoreLabel }}</span>
            </div>
            <div class="progress mt-3" style="height:8px;">
                <div class="progress-bar progress-bar-primary" style="width: {{ max(0, min(100, (int) $browsingScore)) }}%"></div>
            </div>
        </div>

        <div class="row mb-3">
            @foreach ([
                ['label' => 'Most Active Website', 'value' => $mostVisitedWebsite['display_name'] ?? '—'],
                ['label' => 'Current Website', 'value' => $currentWebsiteDomain ?? 'No activity yet'],
                ['label' => 'Current Session', 'value' => $currentSessionLabel ?? '0m'],
                ['label' => 'Website Health Score', 'value' => number_format((int) $websiteHealthScore) . '/100'],
            ] as $metric)
                <div class="col-6 mb-3">
                    <div class="bg-grey rounded p-3">
                        <div class="f-11 text-lightest text-uppercase">{{ $metric['label'] }}</div>
                        <div class="f-14 f-w-500 text-darkest-grey mt-1 text-truncate">{{ $metric['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-grey rounded p-20 bg-white">
            <div class="f-11 text-lightest text-uppercase">Today&apos;s Mix</div>
            <div class="mt-3">
                <div class="d-flex justify-content-between f-14 mb-2">
                    <span class="text-lightest">Research Time</span>
                    <span class="f-w-500 text-darkest-grey">{{ $researchLabel ?? '0m' }}</span>
                </div>
                <div class="d-flex justify-content-between f-14 mb-2">
                    <span class="text-lightest">Distraction Time</span>
                    <span class="f-w-500 text-darkest-grey">{{ $distractionLabel ?? '0m' }}</span>
                </div>
                <div class="d-flex justify-content-between f-14">
                    <span class="text-lightest">Website Health</span>
                    <span class="badge {{ $badgeClass }}">{{ $websiteHealthLabel ?? 'Good' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
