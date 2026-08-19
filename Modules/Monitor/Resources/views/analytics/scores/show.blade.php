@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@section('content')
    <div class="content-wrapper">
        <a href="{{ $backUrl ?? route('monitor.analytics.index', ['tab' => 'scores']) }}" class="f-14 text-dark-grey mb-3 d-inline-block">
            <i class="fa fa-arrow-left f-11 mr-1"></i>@lang('monitor::app.backToScores')
        </a>

        <h3 class="heading-h3 mb-3 f-21 font-weight-bold text-darkest-grey">{{ $employee->name }}</h3>

        <div class="row mb-3">
            <div class="col-xl-3 col-lg-3 col-md-6 mb-3 mb-md-0">
                <x-cards.widget :title="__('monitor::app.todaysScore')" :value="$today_score . '%'" icon="chart-line" />
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 mb-3 mb-md-0">
                <x-cards.widget :title="__('monitor::app.thisWeekAvg')" :value="$week_avg . '%'" icon="calendar-week" />
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 mb-3 mb-md-0">
                <x-cards.widget :title="__('monitor::app.lastWeekAvg')" :value="$last_week_avg . '%'" icon="history" />
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
                <x-cards.widget :title="__('monitor::app.personalBest')" :value="$personal_best . '%'" icon="trophy" />
            </div>
        </div>

        <p class="f-14 text-dark-grey mb-2">
            @lang('monitor::app.weeklyScoreSummary', ['week' => $week_avg, 'last' => $last_week_avg, 'best' => $personal_best])
        </p>
        <p class="f-14 f-w-500 mb-3 {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreTextClass($today_score) }}">{{ $motivation }}</p>

        <x-cards.data class="mb-3" :title="__('monitor::app.thirtyDayTrend')">
            <div class="monitor-mini-bar-chart" style="min-height: 140px; overflow-x: auto;">
                @foreach ($chart as $day)
                    <div class="monitor-mini-bar-col">
                        <span class="f-11 text-lightest mb-1">{{ $day['score'] }}</span>
                        <div class="d-flex align-items-end justify-content-center w-100" style="height: 100px;">
                            <div class="monitor-mini-bar {{ \Modules\Monitor\Services\Analytics\MonitorAnalyticsHelper::scoreBarClass($day['score']) }}"
                                style="height: {{ max($day['bar_pct'], 2) }}%;" title="{{ $day['label'] }}: {{ $day['score'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-2 f-12 text-lightest">
                <span>{{ $chart_start }}</span>
                <span>{{ $chart_end }}</span>
            </div>
        </x-cards.data>

        @if (!empty($browsingSummary['web_seconds']))
            <x-alert type="info" icon="globe" class="mb-3">
                @lang('monitor::app.browsingTimeSummary', [
                    'time' => $browsingSummary['web_label'],
                    'pct' => $browsingSummary['pct_of_tracked'],
                    'domains' => $browsingSummary['unique_domains'],
                ])
            </x-alert>
        @endif

        <x-cards.data class="mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <h4 class="f-14 f-w-500 text-darkest-grey mb-0">@lang('monitor::app.desktopAppsUsage')</h4>
                @include('monitor::analytics.partials.period-selector', [
                    'action' => route('monitor.analytics.scores.show', $employee->id),
                    'period' => $period ?? 'this_week',
                ])
            </div>
            @include('monitor::analytics.partials.app-usage-list', [
                'usage' => $appUsage ?? [],
                'limit' => 10,
                'countLabel' => __('monitor::app.appsCount'),
            ])
        </x-cards.data>

        <x-cards.data class="mb-3">
            <h4 class="f-14 f-w-500 text-darkest-grey mb-3">@lang('monitor::app.websitesBrowsed')</h4>
            @include('monitor::analytics.partials.app-usage-list', [
                'usage' => $websiteUsage ?? [],
                'limit' => 10,
                'countLabel' => __('monitor::app.sitesCount'),
            ])
        </x-cards.data>

        <div class="d-flex flex-wrap align-items-center f-14">
            <a href="{{ route('monitor.analytics.heatmap.show', $employee->id) }}" class="text-primary mr-2">@lang('monitor::app.workPatternHeatmap')</a>
            <span class="text-lightest mr-2">·</span>
            <a href="{{ route('monitor.analytics.idle.show', $employee->id) }}" class="text-primary mr-2">@lang('monitor::app.idleAnalysis')</a>
            <span class="text-lightest mr-2">·</span>
            <a href="{{ route('monitor.show', $employee->id) }}" class="text-primary">@lang('monitor::app.activityDetail')</a>
        </div>
    </div>
@endsection
