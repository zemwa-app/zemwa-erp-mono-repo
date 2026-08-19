@php
    $usage = $usage ?? ['sections' => [], 'section_meta' => [], 'total_count' => 0];
    $limit = $limit ?? 10;
    $showEmployeeRatio = $showEmployeeRatio ?? false;
    $countLabel = $countLabel ?? __('monitor::app.appsCount');
    $sectionOrder = ['productive', 'neutral', 'unproductive'];
    $sectionLabels = [
        'productive' => __('monitor::app.categoryProductive'),
        'neutral' => __('monitor::app.categoryNeutral'),
        'unproductive' => __('monitor::app.categoryUnproductive'),
    ];
    $sectionCardClass = [
        'unproductive' => 'monitor-unproductive-panel',
        'productive' => '',
        'neutral' => '',
    ];
@endphp

<div>
    @foreach ($sectionOrder as $sectionKey)
        @php $items = $usage['sections'][$sectionKey] ?? []; @endphp
        @if (count($items) > 0)
            <div class="card bg-white border-0 b-shadow-4 mb-3 {{ $sectionCardClass[$sectionKey] ?? '' }}">
                <div class="card-body p-20">
                    <h5 class="f-14 f-w-500 text-darkest-grey mb-3">
                        {{ $sectionLabels[$sectionKey] }}
                        <span class="f-w-400 text-lightest">
                            — {{ $usage['section_meta'][$sectionKey . '_count'] ?? count($items) }}
                            {{ $countLabel }}
                            · {{ $usage['section_meta'][$sectionKey . '_label'] ?? '0m' }}
                            @lang('monitor::app.total')
                        </span>
                    </h5>
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
                                        <p class="mb-0 f-12 text-lightest">{{ $item['employee_ratio_label'] }} @lang('monitor::app.employeesShort')</p>
                                    @endif
                                    @if (!empty($item['show_warning']))
                                        <span class="text-warning" title="@lang('monitor::app.deptUnproductiveWarning')">⚠</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    @endforeach

    @if (!empty($usage['sections']['uncategorised']))
        <div class="card bg-white border-0 b-shadow-4 mb-3 bg-additional-grey">
            <div class="card-body p-20">
                <h5 class="f-14 f-w-500 text-dark-grey mb-3">@lang('monitor::app.uncategorised')</h5>
                <ul class="list-group list-group-flush">
                    @foreach (array_slice($usage['sections']['uncategorised'], 0, $limit) as $item)
                        <li class="list-group-item d-flex align-items-center border-0 px-0 py-2">
                            @include('monitor::analytics.partials.app-icon', [
                                'size' => 32,
                                'iconUrl' => $item['icon_url'],
                                'letterAvatar' => $item['letter_avatar'],
                                'alt' => $item['display_name'],
                            ])
                            <div class="ml-3" style="min-width: 0; flex: 1;">
                                <p class="mb-0 f-14 f-w-500 text-dark-grey text-truncate">{{ $item['display_name'] }}</p>
                            </div>
                            @include('monitor::analytics.partials.categorize-inline', ['item' => $item])
                            <span class="ml-3 f-14 text-dark-grey">{{ $item['duration_label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (($usage['total_count'] ?? 0) === 0)
        <p class="f-14 text-lightest text-center p-20 border rounded mb-0">
            @lang('monitor::app.noUsageData')
        </p>
    @endif
</div>
