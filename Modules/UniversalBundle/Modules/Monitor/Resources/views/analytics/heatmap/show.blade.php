@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@section('content')
    <div class="content-wrapper">
        <a href="{{ $backUrl ?? route('monitor.analytics.index', ['tab' => 'scores']) }}" class="f-14 text-dark-grey mb-3 d-inline-block">
            <i class="fa fa-arrow-left f-11 mr-1"></i>@lang('app.back')
        </a>

        <h3 class="heading-h3 mb-3 f-21 font-weight-bold text-darkest-grey">{{ $employee->name }} — @lang('monitor::app.workPatternHeatmap')</h3>

        <ul class="nav nav-pills mb-3">
            @foreach ([30, 60, 90] as $range)
                <li class="{{ $days === $range ? 'active' : '' }}">
                    <a href="{{ route('monitor.analytics.heatmap.show', ['employee' => $employee->id, 'days' => $range]) }}" class="f-12">
                        @lang('monitor::app.lastDays', ['days' => $range])
                    </a>
                </li>
            @endforeach
        </ul>

        @if (!$has_enough_data)
            <p class="f-14 text-lightest text-center p-20 border rounded mb-0">@lang('monitor::app.heatmapInsufficientData')</p>
        @else
            <x-cards.data class="mb-3">
                <div class="table-responsive">
                    <table class="table table-hover w-100 f-12 mb-0">
                        <thead>
                            <tr>
                                <th></th>
                                @foreach ($day_labels as $label)
                                    <th class="text-center f-12 text-dark-grey">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hours as $hour)
                                <tr>
                                    <td class="text-right text-lightest pr-2">{{ sprintf('%02d:00', $hour) }}</td>
                                    @for ($dow = 0; $dow < 7; $dow++)
                                        @php $cell = $cells["{$dow}-{$hour}"] ?? null; @endphp
                                        <td class="p-1">
                                            @if ($cell)
                                                <div class="monitor-heatmap-cell {{ $cell['cell_class'] }}" title="{{ $cell['tooltip'] }}"></div>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="monitor-heatmap-legend mt-3 f-12 text-lightest">
                    <span>@lang('monitor::app.heatmapLegendLow')</span>
                    <span class="bg-teal-100 ml-2 mr-1"></span>
                    <span class="bg-teal-300 mr-1"></span>
                    <span class="bg-teal-500 mr-1"></span>
                    <span class="bg-teal-700 mr-2"></span>
                    <span>@lang('monitor::app.heatmapLegendHigh')</span>
                </div>
            </x-cards.data>

            @if (count($peaks) > 0)
                <div class="mb-3">
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-2">@lang('monitor::app.peakProductiveHours')</h4>
                    <div class="d-flex flex-wrap">
                        @foreach ($peaks as $peak)
                            <div class="card bg-white border-0 b-shadow-4 mr-3 mb-2 p-3">
                                <span class="f-14 f-w-500 text-darkest-grey">{{ $peak['day_label'] }} {{ $peak['hour_label'] }}</span>
                                <span class="f-12 text-dark-grey"> · {{ $peak['avg_score'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
