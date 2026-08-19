@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $items = $items ?? [];

    $toneBarClass = [
        'green' => 'progress-bar-success',
        'yellow' => 'progress-bar-warning',
        'orange' => 'progress-bar-warning',
        'amber' => 'progress-bar-warning',
        'red' => 'progress-bar-danger',
        'gray' => 'progress-bar-secondary',
    ];

    $valueTextClass = [
        'text-success' => 'text-success',
        'text-warning' => 'text-warning',
        'text-danger' => 'text-danger',
        'text-darkest-grey' => 'text-darkest-grey',
        'text-gray-900' => 'text-darkest-grey',
    ];
@endphp

<x-cards.data class="mb-0 h-100" :title="$title">
    @if ($subtitle)
        <p class="f-12 text-lightest mb-3">{{ $subtitle }}</p>
    @endif

    @forelse ($items as $item)
        @php
            $tone = $item['tone'] ?? 'gray';
            $barClass = $toneBarClass[$tone] ?? $toneBarClass['gray'];
            $valueClass = $valueTextClass[$item['value_class'] ?? 'text-darkest-grey'] ?? 'text-darkest-grey';
        @endphp
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mr-3">
                    <p class="mb-0 f-14 text-darkest-grey text-truncate">{{ $item['label'] ?? '' }}</p>
                    @if (!empty($item['description']))
                        <p class="mb-0 f-12 text-lightest">{{ $item['description'] }}</p>
                    @endif
                </div>
                <span class="f-14 font-weight-bold {{ $valueClass }}">{{ $item['value'] ?? 0 }}</span>
            </div>

            @if (array_key_exists('pct', $item))
                <div class="progress mt-2" style="height: 8px; margin-bottom: 0;">
                    <div class="progress-bar {{ $barClass }}" role="progressbar"
                        style="width: {{ max(0, min(100, (float) $item['pct'])) }}%"></div>
                </div>
            @endif
        </div>
    @empty
        <p class="text-center f-14 text-lightest py-4 mb-0">@lang('messages.noRecordFound')</p>
    @endforelse
</x-cards.data>
