@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@section('content')
    <div class="content-wrapper">
        <a href="{{ $backUrl ?? route('monitor.analytics.index', ['tab' => 'departments', 'period' => $period]) }}" class="f-14 text-dark-grey mb-3 d-inline-block">
            <i class="fa fa-arrow-left f-11 mr-1"></i>@lang('app.back')
        </a>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <h3 class="heading-h3 mb-0 f-21 font-weight-bold text-darkest-grey">{{ $department->team_name }}</h3>
            @include('monitor::analytics.partials.period-selector', [
                'action' => route('monitor.analytics.departments.show', $department->id),
                'period' => $period,
            ])
        </div>

        <div class="row mb-3">
            <div class="col-md-4 col-sm-6">
                <x-cards.widget :title="__('monitor::app.departmentAverage')" :value="$avg_score . '%'" icon="chart-pie" />
            </div>
        </div>

        <x-cards.data class="mb-3" :title="__('monitor::app.twelveWeekTrend')">
            <div class="monitor-mini-bar-chart">
                @foreach ($weekly_trend as $week)
                    <div class="monitor-mini-bar-col" title="{{ $week['label'] }}: {{ $week['score'] }}%">
                        <div class="monitor-mini-bar bg-primary" style="height: {{ max(2, $week['score']) }}%;"></div>
                    </div>
                @endforeach
            </div>
        </x-cards.data>

        <h4 class="f-14 f-w-500 text-darkest-grey mb-3">@lang('monitor::app.employeeRanking')</h4>
        @include('monitor::analytics.partials.scores-table', ['rows' => $scores['rows']])

        @if (!empty($dept_apps['items']))
            <x-cards.data class="mb-3" :title="__('monitor::app.mostUsedAppsWeek')">
                @include('monitor::analytics.partials.app-usage-flat', [
                    'items' => $dept_apps['items'],
                    'showEmployeeRatio' => true,
                ])
            </x-cards.data>
        @endif

        @if (!empty($dept_websites['items']))
            <x-cards.data class="mb-3" :title="__('monitor::app.mostUsedWebsitesWeek')">
                @include('monitor::analytics.partials.app-usage-flat', [
                    'items' => $dept_websites['items'],
                    'showEmployeeRatio' => true,
                ])
            </x-cards.data>
        @endif
    </div>
@endsection
