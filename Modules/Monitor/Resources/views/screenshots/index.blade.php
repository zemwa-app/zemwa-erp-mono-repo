@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@push('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('app.employee')</p>
            <div class="select-status">
                <select name="employee" id="filter-employee" class="form-control select-picker" data-live-search="true" data-size="8" data-container="body">
                    <option value="all" @selected($filters['employee'] === 'all')>@lang('app.all')</option>
                    @foreach ($employeeOptions as $employee)
                        <option value="{{ $employee->id }}" @selected((string) $filters['employee'] === (string) $employee->id)>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('app.date')</p>
            <div class="select-status">
                <input type="text"
                    name="date"
                    id="filter-date"
                    value="{{ $filters['date'] }}"
                    class="form-control height-35">
            </div>
        </div>

        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center text-nowrap flex-shrink-0">@lang('app.category')</p>
            <div class="select-status">
                <select name="category" id="filter-category" class="form-control select-picker" data-size="8" data-container="body">
                    <option value="all" @selected($filters['category'] === 'all')>@lang('app.all')</option>
                    <option value="productive" @selected($filters['category'] === 'productive')>@lang('monitor::app.categoryProductive')</option>
                    <option value="neutral" @selected($filters['category'] === 'neutral')>@lang('monitor::app.categoryNeutral')</option>
                    <option value="unproductive" @selected($filters['category'] === 'unproductive')>@lang('monitor::app.categoryUnproductive')</option>
                </select>
            </div>
        </div>

        @include('monitor::partials.filter-search', [
            'id' => 'filter-search',
            'name' => 'search',
            'value' => $filters['search'],
            'placeholder' => __('monitor::app.searchAppName'),
        ])

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs mr-2" type="button" id="apply-screenshot-filters" icon="filter">
                @lang('app.apply')
            </x-forms.button-secondary>
            <x-forms.button-secondary class="btn-xs {{ $filters['employee'] === 'all' && $filters['category'] === 'all' && $filters['search'] === '' && $filters['date'] === now(company()->timezone)->toDateString() ? 'd-none' : '' }}" type="button" id="reset-screenshot-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        @if ($screenshots->total() > 0)
            <p class="f-14 text-dark-grey mb-3">
                <span class="f-w-500 text-darkest-grey">{{ $screenshots->total() }}</span>
                @lang('monitor::app.screenshotResults')
            </p>
        @endif

        @if ($screenshots->isEmpty())
            <div class="card bg-white border-0 b-shadow-4 p-20 text-center">
                <span class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle bg-grey"
                    style="width: 48px; height: 48px;">
                    <i class="fa fa-camera f-16 text-lightest" aria-hidden="true"></i>
                </span>
                <p class="f-14 f-w-500 text-dark-grey mb-0">@lang('monitor::app.noScreenshots')</p>
            </div>
        @else
            <div class="row">
                @foreach ($screenshots as $shot)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                        @include('monitor::screenshots.partials.card', ['shot' => $shot, 'showEmployeeName' => $showEmployeeName])
                    </div>
                @endforeach
            </div>

            @include('monitor::screenshots.partials.pagination', ['paginator' => $screenshots])
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script>
        (function () {
            const previewBaseUrl = @json(route('monitor.screenshot.preview'));
            const screenshotsUrl = @json(route('monitor.screenshots.index'));
            const selectedDate = moment(@json($filters['date']));
            const dateFormat = typeof daterangeLocale !== 'undefined' && daterangeLocale.format
                ? daterangeLocale.format
                : 'YYYY-MM-DD';

            const $form = $('#filter-form');
            $form.attr('method', 'GET').attr('action', screenshotsUrl);

            if (typeof refreshSelectPicker === 'function') {
                refreshSelectPicker('.filter-box .select-picker');
            }

            const $dateInput = $('#filter-date');
            $dateInput.daterangepicker({
                singleDatePicker: true,
                autoUpdateInput: true,
                locale: typeof daterangeLocale !== 'undefined' ? daterangeLocale : {},
                startDate: selectedDate,
            });
            $dateInput.val(selectedDate.format(dateFormat));

            const normalizeFilterDate = function () {
                const picker = $dateInput.data('daterangepicker');
                if (picker) {
                    $dateInput.val(picker.startDate.format('YYYY-MM-DD'));
                }
            };

            $form.on('submit', normalizeFilterDate);

            $('#apply-screenshot-filters').on('click', function (e) {
                e.preventDefault();
                normalizeFilterDate();
                $form.get(0).submit();
            });

            $('body').off('click.monitorScreenshot', '.monitor-screenshot-lightbox')
                .on('click.monitorScreenshot', '.monitor-screenshot-lightbox', function (e) {
                    e.preventDefault();

                    const $el = $(this);
                    const params = new URLSearchParams({
                        image_url: $el.data('image-url') || '',
                        active_app: $el.data('active-app') || '',
                        window_title: $el.data('window-title') || '',
                        captured_at: $el.data('captured-at') || '',
                    });

                    if ($el.data('task-heading')) {
                        params.set('task_heading', $el.data('task-heading'));
                        params.set('task_project', $el.data('task-project') || '');
                        params.set('task_status', $el.data('task-status') || '');
                        params.set('task_priority', $el.data('task-priority') || '');
                        params.set('task_due_date', $el.data('task-due-date') || '');
                        params.set('task_url', $el.data('task-url') || '');
                    }

                    $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_XL, previewBaseUrl + '?' + params.toString());
                });

            $('#reset-screenshot-filters').on('click', function () {
                window.location.href = screenshotsUrl;
            });

            const hasActiveFilters = @json(
                $filters['employee'] !== 'all'
                || $filters['category'] !== 'all'
                || $filters['search'] !== ''
                || $filters['date'] !== now(company()->timezone)->toDateString()
            );
            if (hasActiveFilters) {
                $('#reset-screenshot-filters').removeClass('d-none');
            }
        })();
    </script>
@endpush
