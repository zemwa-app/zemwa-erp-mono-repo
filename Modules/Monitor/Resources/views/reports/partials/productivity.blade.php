<div class="card-header bg-white border-bottom-grey p-20">
    <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.productivitySummary')</h4>
    <p class="f-12 text-lightest mb-0 mt-1">
        @lang('monitor::app.weekOf') {{ collect($report['work_week'])->first()['short'] ?? '' }}
        — {{ collect($report['work_week'])->last()['short'] ?? '' }}
        · @lang('monitor::app.metric'):
        @switch($filters['metric'])
            @case('active_time') @lang('monitor::app.activeTime') @break
            @case('idle_time') @lang('monitor::app.idleTime') @break
            @case('screenshots') @lang('monitor::app.screenshots') @break
            @default @lang('monitor::app.productivity')
        @endswitch
    </p>
</div>
<div class="card-body p-20">
    <div class="table-responsive">
        <table class="table table-hover w-100 f-14 mb-0">
            <thead>
                <tr class="text-uppercase f-11 text-lightest">
                    <th>@lang('app.employee')</th>
                    @foreach ($report['work_week'] as $day)
                        <th class="text-center" title="{{ $day['date'] }}">{{ $day['label'] }}</th>
                    @endforeach
                    <th class="text-center">@lang('monitor::app.avg')</th>
                    <th class="text-center">@lang('monitor::app.trend')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td>
                            <span class="f-w-500 text-darkest-grey d-block">{{ $row['name'] }}</span>
                            <span class="f-12 text-lightest">{{ $row['department'] }}</span>
                        </td>
                        @foreach ($report['work_week'] as $day)
                            @php $cell = $row['days'][$day['key']]; @endphp
                            <td class="text-center">
                                <span class="badge {{ $cell['class'] }}"
                                    style="min-width: 52px;"
                                    title="{{ $day['short'] }}">
                                    {{ $cell['display'] }}
                                </span>
                            </td>
                        @endforeach
                        <td class="text-center">
                            <span class="badge {{ $row['avg']['class'] }}" style="min-width: 52px;">
                                {{ $row['avg']['display'] }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fa fa-{{ $row['trend']['icon'] }} f-12 mr-2 {{ $row['trend']['class'] }}"></i>
                                <div class="monitor-sparkline">
                                    @foreach ($row['trend']['sparkline'] as $bar)
                                        <div class="monitor-sparkline-bar" style="height: {{ max($bar * 0.24, 2) }}px;"></div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report['work_week']) + 3 }}" class="text-center py-5 f-14 text-lightest">
                            @lang('messages.noRecordFound')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
