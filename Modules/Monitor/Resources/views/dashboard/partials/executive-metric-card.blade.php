@php
    $title = $title ?? '';
    $value = $value ?? '—';
    $subtitle = $subtitle ?? null;
    $statusLabel = $statusLabel ?? null;
    $statusTone = $statusTone ?? 'gray';
    $trend = $trend ?? null;
    $progress = isset($progress) ? (float) $progress : null;
    $showRing = (bool) ($showRing ?? false);

    $badgeClasses = [
        'green' => 'badge badge-success',
        'yellow' => 'badge badge-warning',
        'orange' => 'badge badge-warning',
        'red' => 'badge badge-danger',
        'gray' => 'badge badge-secondary',
    ];
@endphp

<div class="bg-white p-20 rounded b-shadow-4 h-100">
    <div class="d-flex justify-content-between align-items-start">
        <div class="w-100">
            <p class="f-12 text-lightest text-uppercase mb-2">{{ $title }}</p>
            <div class="d-flex align-items-end">
                <span class="f-21 font-weight-bold text-darkest-grey">{{ $value }}</span>
                @if ($trend)
                    <span class="f-12 text-lightest ml-2">{{ $trend['label'] ?? '' }}</span>
                @endif
            </div>
            @if ($subtitle)
                <p class="f-12 text-lightest mt-2 mb-0">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($showRing && $progress !== null)
            @php
                $progress = max(0, min(100, $progress));
                $radius = 18;
                $circumference = 2 * pi() * $radius;
                $offset = $circumference - (($progress / 100) * $circumference);
                $stroke = match ($statusTone) {
                    'green' => '#22c55e',
                    'yellow' => '#eab308',
                    'orange' => '#f97316',
                    'red' => '#ef4444',
                    default => '#6b7280',
                };
            @endphp
            <div class="position-relative ml-3" style="width: 48px; height: 48px; flex-shrink: 0;">
                <svg viewBox="0 0 48 48" style="width: 48px; height: 48px; transform: rotate(-90deg);">
                    <circle cx="24" cy="24" r="{{ $radius }}" fill="none" stroke="#e5e7eb" stroke-width="5"></circle>
                    <circle cx="24" cy="24" r="{{ $radius }}" fill="none" stroke="{{ $stroke }}" stroke-width="5"
                        stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"></circle>
                </svg>
                <div class="position-absolute d-flex align-items-center justify-content-center w-100 h-100" style="top: 0; left: 0;">
                    <span class="f-11 font-weight-bold text-darkest-grey">{{ number_format($progress, 0) }}%</span>
                </div>
            </div>
        @endif
    </div>

    @if ($statusLabel)
        <span class="{{ $badgeClasses[$statusTone] ?? $badgeClasses['gray'] }} mt-3 d-inline-block">{{ $statusLabel }}</span>
    @endif
</div>
