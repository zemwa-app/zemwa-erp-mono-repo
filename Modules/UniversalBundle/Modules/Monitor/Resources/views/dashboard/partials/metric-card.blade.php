@php
    $title = $title ?? '';
    $value = $value ?? '—';
    $caption = $caption ?? null;
    $compact = (bool) ($compact ?? false);
    $lines = $lines ?? [];
    $trend = $trend ?? null;
    $ring = $ring ?? null;
    $valueSizeClass = $compact ? 'f-18' : 'f-21';
@endphp

<div class="bg-white p-20 rounded b-shadow-4 h-100">
    <div class="d-flex justify-content-between align-items-start">
        <div class="w-100">
            <p class="f-12 text-lightest text-uppercase mb-2">{{ $title }}</p>
            <div class="d-flex align-items-end">
                <span class="{{ $valueSizeClass }} font-weight-bold text-darkest-grey">{{ $value }}</span>
                @if ($trend)
                    <span class="f-12 text-lightest ml-2">{{ $trend['label'] ?? '' }}</span>
                @endif
            </div>
            @if ($caption)
                <p class="f-12 text-lightest mt-2 mb-0">{{ $caption }}</p>
            @endif

            @if (!empty($lines))
                <div class="mt-3">
                    @foreach ($lines as $line)
                        <div class="d-flex justify-content-between f-12 mb-1">
                            <span class="text-lightest">{{ $line['label'] ?? '' }}</span>
                            <span class="font-weight-bold text-darkest-grey">{{ $line['value'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($ring)
            @php
                $score = (float) ($ring['value'] ?? 0);
                $score = max(0, min(100, $score));
                $radius = 22;
                $circumference = 2 * pi() * $radius;
                $offset = $circumference - (($score / 100) * $circumference);
                $stroke = match (true) {
                    $score >= 80 => '#22c55e',
                    $score >= 60 => '#eab308',
                    $score >= 40 => '#f97316',
                    default => '#ef4444',
                };
            @endphp
            <div class="ml-3 text-center" style="flex-shrink: 0;">
                <div class="position-relative" style="width: 64px; height: 64px;">
                    <svg viewBox="0 0 56 56" style="width: 64px; height: 64px; transform: rotate(-90deg);">
                        <circle cx="28" cy="28" r="{{ $radius }}" fill="none" stroke="#e5e7eb" stroke-width="6"></circle>
                        <circle cx="28" cy="28" r="{{ $radius }}" fill="none" stroke="{{ $stroke }}" stroke-width="6"
                            stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"></circle>
                    </svg>
                    <div class="position-absolute d-flex flex-column align-items-center justify-content-center w-100 h-100" style="top: 0; left: 0;">
                        <span class="f-14 font-weight-bold text-darkest-grey">{{ number_format($score, 0) }}%</span>
                        @if (!empty($ring['label']))
                            <span class="f-10 text-lightest text-uppercase">{{ $ring['label'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
