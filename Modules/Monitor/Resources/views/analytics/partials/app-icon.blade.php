@php
    $size = (int) ($size ?? 32);
    $iconUrl = $iconUrl ?? null;
    $alt = $alt ?? '';
    $letterAvatar = is_array($letterAvatar ?? null) ? $letterAvatar : ['letter' => '?', 'color' => '#475569'];
    $letter = $letterAvatar['letter'] ?? '?';
    $color = $letterAvatar['color'] ?? '#475569';
    $fontSize = max(10, (int) round($size * 0.42));
@endphp
<span class="monitor-app-icon" style="width: {{ $size }}px; height: {{ $size }}px;">
    <span class="monitor-app-icon-letter"
        style="background-color: {{ $color }}; font-size: {{ $fontSize }}px; line-height: 1;"
        aria-hidden="{{ $iconUrl ? 'true' : 'false' }}">{{ $letter }}</span>
    @if ($iconUrl)
        <img src="{{ $iconUrl }}" alt="{{ $alt }}" width="{{ $size }}" height="{{ $size }}"
            loading="lazy" referrerpolicy="no-referrer"
            onerror="this.remove();">
    @endif
</span>
