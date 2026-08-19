@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@push('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
@endpush

@php
    $tabs = [
        'overview' => ['label' => __('monitor::app.overview'), 'icon' => 'fa-chart-pie'],
        'apps' => ['label' => __('monitor::app.activeApps'), 'icon' => 'fa-th-large'],
        'websites' => ['label' => __('monitor::app.websitesBrowsed'), 'icon' => 'fa-globe'],
        'timeline' => ['label' => __('monitor::app.timeline'), 'icon' => 'fa-stream'],
        'screenshots' => ['label' => __('monitor::app.screenshots'), 'icon' => 'fa-camera'],
        'network' => ['label' => __('monitor::app.network'), 'icon' => 'fa-network-wired'],
        'events' => ['label' => __('monitor::app.eventsTab'), 'icon' => 'fa-bell'],
    ];
@endphp

@section('filter-section')
    <div class="d-flex d-lg-block filter-box project-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>

        <div class="d-flex align-items-center justify-content-between border-bottom-grey p-20 w-100">
            <div class="d-flex flex-wrap align-items-center flex-grow-1">
                <a href="{{ route('monitor.index') }}" class="f-14 text-dark-grey mr-3">
                    <i class="fa fa-arrow-left mr-1" aria-hidden="true"></i>
                    @lang('monitor::app.back')
                </a>
                <span class="text-lightest mr-2 d-none d-sm-inline" aria-hidden="true">|</span>
                <h2 class="f-16 f-w-500 text-darkest-grey mb-0 mr-2 text-truncate">
                    {{ $employee->name }}
                </h2>
                <span class="f-12 text-lightest">@lang('monitor::app.activityDetail')</span>
            </div>

            <div class="d-flex align-items-center mt-2 mt-lg-0">
                <label for="monitor-detail-date" class="sr-only">@lang('app.date')</label>
                <input type="text"
                    class="form-control height-35"
                    id="monitor-detail-date"
                    value="{{ $selectedDate }}"
                    placeholder="@lang('placeholders.date')">
            </div>
        </div>

        <div class="project-menu" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <x-tab-section class="monitor-detail-tabs mb-0">
                @foreach ($tabs as $key => $tab)
                    @php
                        $isActive = $activeTab === $key;
                        $href = route('monitor.show', $employee->id) . '?tab=' . $key . '&date=' . $selectedDate;
                    @endphp
                    <x-tab-item :active="$isActive" :link="$href">
                        <i class="fa {{ $tab['icon'] }} mr-2 f-13" aria-hidden="true"></i>
                        <span>{{ $tab['label'] }}</span>
                    </x-tab-item>
                @endforeach
            </x-tab-section>
        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey openClientDetailSidebar" href="javascript:;">
            <i class="fa fa-ellipsis-v"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="content-wrapper pt-0 border-top-0 client-detail-wrapper">
        <div class="monitor-detail-panel">
            @include($view)
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script>
        (function () {
            const activeTab = @json($activeTab);
            const screenshotTask = @json(($activeTab ?? '') === 'screenshots' ? ($screenshotTaskFilter ?? 'all') : '');

            const $dateInput = $('#monitor-detail-date');
            const selectedDate = moment(@json($selectedDate));
            const dateFormat = typeof daterangeLocale !== 'undefined' && daterangeLocale.format
                ? daterangeLocale.format
                : 'YYYY-MM-DD';

            $dateInput.daterangepicker({
                singleDatePicker: true,
                autoUpdateInput: true,
                locale: typeof daterangeLocale !== 'undefined' ? daterangeLocale : {},
                startDate: selectedDate,
            });

            $dateInput.val(selectedDate.format(dateFormat));

            $dateInput.on('apply.daterangepicker', function (ev, picker) {
                const date = picker.startDate.format('YYYY-MM-DD');
                let url = "{{ route('monitor.show', $employee->id) }}"
                    + '?tab=' + activeTab
                    + '&date=' + encodeURIComponent(date);

                if (activeTab === 'screenshots' && screenshotTask && screenshotTask !== 'all') {
                    url += '&task=' + encodeURIComponent(screenshotTask);
                }

                window.location.href = url;
            });

            $('#close-client-overlay, #close-client-detail').on('click', function () {
                $('#mob-client-detail').removeClass('open');
                $('#close-client-overlay').addClass('d-none');
            });

            $('body').on('click', '.openClientDetailSidebar', function () {
                $('#mob-client-detail').addClass('open');
                $('#close-client-overlay').removeClass('d-none');
            });
        })();
    </script>
@endpush
