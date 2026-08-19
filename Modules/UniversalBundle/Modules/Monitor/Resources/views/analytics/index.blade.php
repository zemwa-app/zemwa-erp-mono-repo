@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@php
    use Modules\Monitor\Services\Analytics\PeriodHelper;
    $analyticsTabs = [
        'scores' => __('monitor::app.scoresRanking'),
        'departments' => __('monitor::app.departmentsAnalytics'),
        'compliance' => __('monitor::app.complianceAnalytics'),
        'projects' => __('monitor::app.projectTime'),
    ];
    $showPeriod = $activeTab !== 'compliance';
@endphp

@section('filter-section')
    <x-filters.filter-box>
        @if ($showPeriod)
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
                <div class="select-status">
                    <select name="period" id="analytics-period" class="form-control select-picker" data-size="8" data-container="body">
                        @foreach (PeriodHelper::options() as $key => $label)
                            <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 f-12 text-lightest d-flex align-items-center">@lang('monitor::app.complianceBasedOn7Days')</p>
            </div>
        @endif

        <input type="hidden" name="tab" id="analytics-tab" value="{{ $activeTab }}">
        @if ($activeTab === 'scores')
            <input type="hidden" name="department" id="analytics-department" value="{{ $department_id }}">
            <input type="hidden" name="below_sixty" id="analytics-below-sixty" value="{{ $below_sixty_only ? '1' : '0' }}">
        @endif

        @include('monitor::partials.filter-search', [
            'id' => 'analytics-search',
            'value' => $filters['search'] ?? request('search', ''),
            'placeholder' => match ($activeTab) {
                'departments' => __('monitor::app.searchTeam'),
                'projects' => __('monitor::app.searchProject'),
                'compliance' => __('monitor::app.searchEmployee'),
                default => __('monitor::app.searchEmployee'),
            },
        ])

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs" type="button" id="apply-analytics-filters" icon="filter">
                @lang('app.apply')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <x-tab-section class="monitor-analytics-tabs mb-3">
            @foreach ($analyticsTabs as $tabKey => $tabLabel)
                <x-tab-item
                    :active="$activeTab === $tabKey"
                    link="javascript:;"
                    class="analytics-tab-link"
                    data-analytics-tab="{{ $tabKey }}">
                    {{ $tabLabel }}
                </x-tab-item>
            @endforeach
        </x-tab-section>

        <div class="card bg-white border-0 b-shadow-4 mb-3">
            <div class="card-body p-0">
                @if ($activeTab === 'scores')
                    @include('monitor::analytics.partials.scores-tab')
                @elseif ($activeTab === 'departments')
                    @include('monitor::analytics.partials.departments-tab')
                @elseif ($activeTab === 'compliance')
                    @include('monitor::analytics.partials.compliance-tab')
                @else
                    @include('monitor::analytics.partials.projects-tab')
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const analyticsUrl = @json(route('monitor.analytics.index'));
            const $form = $('#filter-form');
            $form.attr('method', 'GET').attr('action', analyticsUrl);

            if (typeof refreshSelectPicker === 'function') {
                refreshSelectPicker('.filter-box .select-picker');
            }

            const submitFilters = () => $form.get(0).submit();

            $('#apply-analytics-filters').on('click', function (e) {
                e.preventDefault();
                submitFilters();
            });

            $('body').off('click.monitorAnalytics', '.analytics-tab-link')
                .on('click.monitorAnalytics', '.analytics-tab-link', function () {
                    $('#analytics-tab').val($(this).data('analytics-tab'));
                    submitFilters();
                });
        })();
    </script>
@endpush
