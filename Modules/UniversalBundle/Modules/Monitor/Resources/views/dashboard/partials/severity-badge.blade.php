@php
    $tone = $tone ?? 'amber';
    $label = $label ?? 'Medium';

    $badgeClasses = [
        'critical' => 'badge badge-danger',
        'high' => 'badge badge-warning',
        'medium' => 'badge badge-warning',
        'low' => 'badge badge-secondary',
        'red' => 'badge badge-danger',
        'orange' => 'badge badge-warning',
        'amber' => 'badge badge-warning',
        'gray' => 'badge badge-secondary',
    ];
@endphp

<span class="{{ $badgeClasses[$tone] ?? $badgeClasses['medium'] }}">
    {{ $label }}
</span>
