@php
    $status = $status ?? 'offline';
    $label = $label ?? ucfirst($status);
    $compact = (bool) ($compact ?? false);

    $badgeClasses = [
        'online' => 'badge badge-success',
        'idle' => 'badge badge-warning',
        'paused' => 'badge badge-warning',
        'offline' => 'badge badge-secondary',
    ];
@endphp

<span class="{{ $badgeClasses[$status] ?? $badgeClasses['offline'] }} {{ $compact ? 'f-11' : '' }}">
    {{ $label }}
</span>
