@php
    $itemCount = count($items);
    $badgeClass = match ($tone ?? 'gray') {
        'green' => 'badge-success',
        'amber' => 'badge-warning',
        default => 'badge-secondary',
    };
    $itemAccentClass = match ($tone ?? 'gray') {
        'green' => 'monitor-insight-item--positive',
        'amber' => 'monitor-insight-item--attention',
        default => 'monitor-insight-item--neutral',
    };
    $columnClasses = trim(implode(' ', array_filter([
        'col-lg-6',
        'monitor-insight-column',
        'p-20',
        'border-bottom-grey',
        !empty($borderRight) ? 'border-right-grey border-right-grey-sm-0' : null,
    ])));
@endphp

<div class="{{ $columnClasses }}">
    <div class="d-flex align-items-start justify-content-between mb-3">
        <div>
            <h4 class="f-14 f-w-500 text-darkest-grey mb-0">{{ $title }}</h4>
            <p class="f-12 text-lightest mb-0 mt-1">{{ $subtitle }}</p>
        </div>
        <span class="badge {{ $badgeClass }} flex-shrink-0 ml-2">
            {{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}
        </span>
    </div>
    <div>
        @forelse ($items as $item)
            <div class="monitor-insight-item {{ $itemAccentClass }} mb-2">
                <p class="f-14 f-w-500 text-darkest-grey mb-0">{{ $item['label'] }}</p>
                @if (!empty($item['detail']))
                    <p class="f-12 text-lightest mb-0 mt-1">{{ $item['detail'] }}</p>
                @endif
            </div>
        @empty
            <div class="monitor-insight-empty f-14 text-lightest text-center mb-0">
                {{ $emptyText }}
            </div>
        @endforelse
    </div>
</div>
