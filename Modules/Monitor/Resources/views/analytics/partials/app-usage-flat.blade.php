@php
    $items = $items ?? [];
    $showEmployeeRatio = $showEmployeeRatio ?? false;
@endphp
<ul class="list-group list-group-flush">
    @foreach ($items as $item)
        <li class="list-group-item d-flex align-items-center border-0 px-0 py-2">
            @include('monitor::analytics.partials.app-icon', [
                'size' => 32,
                'iconUrl' => $item['icon_url'],
                'letterAvatar' => $item['letter_avatar'],
                'alt' => $item['display_name'],
            ])
            <div class="ml-3" style="min-width: 0; flex: 1;">
                <p class="mb-0 f-14 f-w-500 text-darkest-grey text-truncate">{{ $item['display_name'] }}</p>
                @if (!empty($item['subcategory_label']))
                    <p class="mb-0 f-12 text-lightest">{{ $item['subcategory_label'] }}</p>
                @endif
            </div>
            <div class="d-none d-sm-block ml-3" style="width: 120px;">
                <div class="monitor-usage-progress">
                    <div class="progress-bar bg-primary" style="width: {{ $item['bar_pct'] }}%"></div>
                </div>
            </div>
            <div class="text-right ml-3 f-14 text-dark-grey">
                <span class="f-w-500">{{ $item['duration_label'] }}</span>
                @if ($showEmployeeRatio && !empty($item['employee_ratio_label']))
                    <p class="mb-0 f-12 text-lightest">{{ $item['employee_ratio_label'] }}</p>
                @endif
                @if (!empty($item['show_warning']))
                    <span class="text-warning" title="@lang('monitor::app.deptUnproductiveWarning')">⚠</span>
                @endif
            </div>
        </li>
    @endforeach
</ul>
