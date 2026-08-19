@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@push('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
@endpush

@section('content')
    <div class="content-wrapper">
        <a href="{{ $backUrl ?? route('monitor.analytics.index', ['tab' => 'scores']) }}" class="f-14 text-dark-grey mb-3 d-inline-block">
            <i class="fa fa-arrow-left f-11 mr-1"></i>@lang('app.back')
        </a>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <h3 class="heading-h3 mb-0 f-21 font-weight-bold text-darkest-grey">{{ $employee->name }} — @lang('monitor::app.idleAnalysis')</h3>
            <div class="d-flex align-items-center">
                <a href="{{ route('monitor.analytics.idle.show', ['employee' => $employee->id, 'date' => $prev_date]) }}" class="btn btn-secondary btn-xs mr-2"><i class="fa fa-chevron-left"></i></a>
                <input type="text" id="idle-date" value="{{ $date }}" class="form-control f-14 mr-2" style="width: 140px;">
                <a href="{{ route('monitor.analytics.idle.show', ['employee' => $employee->id, 'date' => $next_date]) }}" class="btn btn-secondary btn-xs"><i class="fa fa-chevron-right"></i></a>
            </div>
        </div>

        @if (!empty($anomalies))
            <x-alert type="warning" icon="exclamation-triangle" class="mb-3">
                <p class="f-w-500 mb-1">@lang('monitor::app.anomalyDetected')</p>
                <ul class="mb-0 pl-3">
                    @foreach ($anomalies as $msg)
                        <li class="f-14">{{ $msg }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-cards.data class="mb-3">
            <div class="monitor-idle-bar mb-2">
                <div class="monitor-idle-active bg-primary" style="width: {{ $active_pct }}%"></div>
            </div>
            <p class="mb-0 f-14 text-dark-grey">
                @lang('monitor::app.activeIdleSummary', ['active' => $active_label, 'idle' => $idle_label, 'total' => $total_label])
            </p>
        </x-cards.data>

        <div class="table-responsive mb-3">
            <table class="table table-hover w-100">
                <thead>
                    <tr>
                        <th class="f-12 text-dark-grey">@lang('monitor::app.startTime')</th>
                        <th class="f-12 text-dark-grey">@lang('monitor::app.endTime')</th>
                        <th class="f-12 text-dark-grey">@lang('app.duration')</th>
                        <th class="f-12 text-dark-grey">@lang('app.label')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td class="f-14">{{ $period['start'] }}</td>
                            <td class="f-14">{{ $period['end'] }}</td>
                            <td class="f-14">{{ $period['duration'] }}</td>
                            <td class="f-14">
                                <span class="badge {{ $period['label_class'] }}">{{ $period['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="f-14 text-lightest text-center p-20">@lang('monitor::app.noIdlePeriods')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <h4 class="f-14 f-w-500 text-darkest-grey mb-2">@lang('monitor::app.thisWeekSummary')</h4>
            <div class="d-flex flex-wrap">
                @foreach ($week_summary as $day)
                    <div class="card bg-white border mb-2 mr-2 p-3 monitor-week-day {{ $day['is_today'] ? 'is-today' : '' }}">
                        <p class="f-12 f-w-500 text-darkest-grey mb-1">{{ $day['label'] }}</p>
                        <p class="mb-0 f-12 text-dark-grey">{{ $day['active_pct'] !== null ? $day['active_pct'] . '%' : '—' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script>
        $('#idle-date').daterangepicker({ singleDatePicker: true, locale: typeof daterangeLocale !== 'undefined' ? daterangeLocale : {} });
        $('#idle-date').on('apply.daterangepicker', function (ev, picker) {
            window.location.href = "{{ route('monitor.analytics.idle.show', $employee->id) }}?date=" + picker.startDate.format('YYYY-MM-DD');
        });
    </script>
@endpush
