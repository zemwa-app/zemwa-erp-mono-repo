@php
    use Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper;
    $score = (float) ($score ?? 0);
    $reference = isset($referenceScore) ? (float) $referenceScore : null;
@endphp
<div class="monitor-score-bar position-relative" style="min-width: 120px;">
    <div class="monitor-usage-progress">
        <div class="progress-bar {{ MonitorAnalyticsHelper::scoreBarClass($score) }}" style="width: {{ min(100, max(0, $score)) }}%"></div>
    </div>
    @if ($reference !== null)
        <div class="monitor-score-reference" style="left: {{ min(100, max(0, $reference)) }}%;"></div>
    @endif
</div>
