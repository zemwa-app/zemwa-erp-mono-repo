@php
    $score = (float) ($score ?? 0);
    $reference = isset($referenceScore) ? (float) $referenceScore : null;
    $showScore = (bool) ($showScore ?? true);

    if ($score >= 80) {
        $barClass = 'progress-bar-success';
        $textClass = 'text-success';
    } elseif ($score >= 60) {
        $barClass = 'progress-bar-warning';
        $textClass = 'text-warning';
    } elseif ($score >= 40) {
        $barClass = 'progress-bar-warning';
        $textClass = 'text-warning';
    } else {
        $barClass = 'progress-bar-danger';
        $textClass = 'text-danger';
    }
@endphp

<div class="w-100" style="min-width: 120px;">
    <div class="progress position-relative" style="height: 8px; margin-bottom: 0;">
        <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ max(0, min(100, $score)) }}%"></div>
        @if ($reference !== null)
            <div class="position-absolute border-left" style="top: 0; bottom: 0; left: {{ max(0, min(100, $reference)) }}%; border-left: 2px dashed #9ca3af !important;"></div>
        @endif
    </div>
    @if ($showScore)
        <div class="text-right mt-1">
            <span class="f-11 font-weight-bold {{ $textClass }}">{{ number_format($score, 1) }}%</span>
        </div>
    @endif
</div>
