@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@push('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="w-100 monitor-reports-filter-bar">
            <div class="d-flex flex-wrap align-items-center monitor-reports-filter-bar__primary">
                <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('app.employee')</p>
                    <div class="select-status monitor-reports-filter-bar__employee">
                        <select name="employee[]" id="filter-employees" class="form-control select-picker" multiple data-live-search="true" data-size="8" data-container="body">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(in_array($employee->id, $filters['employee_ids'], true))>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('app.menu.teams')</p>
                    <div class="select-status">
                        <select name="department" id="filter-department" class="form-control select-picker" data-live-search="true" data-size="8" data-container="body">
                            <option value="all" @selected($filters['department'] === 'all')>@lang('app.all')</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) $filters['department'] === (string) $team->id)>
                                    {{ $team->team_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('app.duration')</p>
                    <div class="select-status d-flex monitor-reports-filter-bar__duration">
                        <input type="hidden" name="start_date" id="start-date" value="{{ $filters['start_date'] }}">
                        <input type="hidden" name="end_date" id="end-date" value="{{ $filters['end_date'] }}">
                        <input type="text"
                            id="report-date-range"
                            class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey height-35"
                            placeholder="@lang('placeholders.dateRange')">
                    </div>
                </div>

                <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('monitor::app.metric')</p>
                    <div class="select-status">
                        <select name="metric" id="filter-metric" class="form-control select-picker" data-size="8" data-container="body">
                            <option value="productivity" @selected($filters['metric'] === 'productivity')>@lang('monitor::app.productivity')</option>
                            <option value="active_time" @selected($filters['metric'] === 'active_time')>@lang('monitor::app.activeTime')</option>
                            <option value="idle_time" @selected($filters['metric'] === 'idle_time')>@lang('monitor::app.idleTime')</option>
                            <option value="screenshots" @selected($filters['metric'] === 'screenshots')>@lang('monitor::app.screenshots')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center monitor-reports-filter-bar__secondary border-top-grey">
                <input type="hidden" name="tab" id="filter-tab" value="{{ $filters['tab'] }}">

                @include('monitor::partials.filter-search', [
                    'id' => 'filter-search',
                    'name' => 'search',
                    'value' => $filters['search'] ?? '',
                    'placeholder' => __('monitor::app.searchReportRows'),
                    'wrapperClass' => 'flex-grow-1 monitor-reports-filter-search px-0 px-lg-2',
                    'showBorder' => false,
                ])

                <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0 flex-shrink-0 ml-auto">
                    <x-forms.button-secondary class="btn-xs mr-2" type="button" id="apply-report-filters" icon="filter">
                        @lang('app.apply')
                    </x-forms.button-secondary>
                    <x-forms.button-secondary class="btn-xs {{ $hasActiveFilters ? '' : 'd-none' }}" type="button" id="reset-report-filters" icon="times-circle">
                        @lang('app.clearFilters')
                    </x-forms.button-secondary>
                </div>
            </div>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        @php
            $reportTabs = [
                'productivity' => __('monitor::app.productivitySummary'),
                'app_usage' => __('monitor::app.appUsage'),
                'website_usage' => __('monitor::app.websiteUsage'),
                'idle' => __('monitor::app.idleAnalysis'),
                'screenshots' => __('monitor::app.screenshotsSummary'),
            ];
        @endphp

        <div class="row align-items-center mb-3">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <ul class="nav nav-pills monitor-report-tabs flex-wrap mb-0" role="tablist">
                @foreach ($reportTabs as $tabKey => $tabLabel)
                    <li class="nav-item mr-1 mb-1" role="presentation">
                        <button type="button"
                            role="tab"
                            aria-selected="{{ $filters['tab'] === $tabKey ? 'true' : 'false' }}"
                            data-report-tab="{{ $tabKey }}"
                            class="nav-link report-tab-link {{ $filters['tab'] === $tabKey ? 'active' : '' }}">
                            {{ $tabLabel }}
                        </button>
                    </li>
                @endforeach
                </ul>
            </div>

            <div class="col-lg-4 text-lg-right">
                <a href="{{ route('monitor.reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
                    class="btn btn-secondary btn-sm mr-2 mb-2 mb-lg-0">
                    <i class="fa fa-file-excel text-success mr-1" aria-hidden="true"></i>
                    @lang('monitor::app.exportCsv')
                </a>
                <a href="{{ route('monitor.reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                    class="btn btn-secondary btn-sm">
                    <i class="fa fa-file-pdf text-danger mr-1" aria-hidden="true"></i>
                    @lang('monitor::app.exportPdf')
                </a>
            </div>
        </div>

        <div class="card bg-white border-0 b-shadow-4 mb-0">
            @if ($filters['tab'] === 'productivity')
                @include('monitor::reports.partials.productivity')
            @elseif ($filters['tab'] === 'app_usage')
                @include('monitor::reports.partials.app-usage')
            @elseif ($filters['tab'] === 'website_usage')
                @include('monitor::reports.partials.website-usage')
            @elseif ($filters['tab'] === 'idle')
                @include('monitor::reports.partials.idle')
            @else
                @include('monitor::reports.partials.screenshots')
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script>
        (function () {
            const reportsUrl = @json(route('monitor.reports.index'));
            const $form = $('#filter-form');
            $form.attr('method', 'GET').attr('action', reportsUrl);

            if (typeof refreshSelectPicker === 'function') {
                refreshSelectPicker('.filter-box .select-picker');
            }

            const startDate = moment(@json($filters['start_date']));
            const endDate = moment(@json($filters['end_date']));

            $('#report-date-range').daterangepicker({
                autoUpdateInput: true,
                locale: typeof daterangeLocale !== 'undefined' ? daterangeLocale : {},
                startDate: startDate,
                endDate: endDate,
            }, function (start, end) {
                $('#start-date').val(start.format('YYYY-MM-DD'));
                $('#end-date').val(end.format('YYYY-MM-DD'));
            });

            $('#start-date').val(startDate.format('YYYY-MM-DD'));
            $('#end-date').val(endDate.format('YYYY-MM-DD'));

            const submitFilters = () => $form.get(0).submit();

            $('#apply-report-filters').on('click', function (e) {
                e.preventDefault();
                submitFilters();
            });

            $('body').off('click.monitorReports', '.report-tab-link')
                .on('click.monitorReports', '.report-tab-link', function () {
                    $('#filter-tab').val($(this).data('report-tab'));
                    submitFilters();
                });

            $('#reset-report-filters').on('click', function () {
                window.location.href = reportsUrl;
            });
        })();
    </script>
@endpush
