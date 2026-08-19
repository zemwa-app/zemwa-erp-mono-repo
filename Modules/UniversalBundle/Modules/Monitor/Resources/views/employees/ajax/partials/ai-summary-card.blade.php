@php
    $summary = $summary ?? [];
    $totalBrowsingSeconds = (int) ($summary['total_active_seconds'] ?? 0);
    $topDomains = $topDomains ?? null;
    $aiSummary = $aiSummary ?? ($summary['ai_summary'] ?? 'No unusual browsing behavior detected.');
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3">
    <div class="card-header bg-white border-bottom-grey p-20">
        <h4 class="f-14 f-w-500 text-darkest-grey mb-0">AI Browsing Summary</h4>
        <p class="f-12 text-lightest mb-0 mt-1">Conclusions first, data second.</p>
    </div>
    <div class="card-body p-20">
        <div class="bg-grey rounded p-20 mb-3">
            <p class="f-14 text-dark-grey mb-0">“{{ $aiSummary }}”</p>
        </div>
        <div class="d-flex flex-wrap">
            <span class="badge badge-secondary mr-2 mb-2">{{ \Modules\Monitor\Services\MonitorEmployeeDetailService::formatDuration((int) $totalBrowsingSeconds) }} total browsing</span>
            @if (!empty($topDomains))
                <span class="badge badge-secondary mb-2">Top domains: {{ \Illuminate\Support\Str::limit($topDomains, 56) }}</span>
            @endif
        </div>
    </div>
</div>
