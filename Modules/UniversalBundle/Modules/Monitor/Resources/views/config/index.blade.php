@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@push('datatable-styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="alert alert-info f-14 mb-4" role="alert">
            <i class="fa fa-info-circle mr-1" aria-hidden="true"></i>
            @lang('monitor::app.configPageIntro')
        </div>

        @include('monitor::config.partials.monitor-seats')

        <div class="card bg-white border-0 b-shadow-4 mb-4">
            <div class="card-body p-20">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.categoryRulesTitle')</h4>
                        <p class="f-12 text-lightest mb-0 mt-1">@lang('monitor::app.categoryRulesCardIntro')</p>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <a href="{{ route('monitor.config.rules.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-tags mr-1"></i>@lang('monitor::app.manageCategories')
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-white border-0 b-shadow-4 mb-4">
            <div class="card-header bg-white border-bottom-grey p-20">
                <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.orgWideDefaults')</h4>
                <p class="f-12 text-lightest mb-0 mt-1">@lang('monitor::app.orgWideDefaultsHelp')</p>
            </div>
            <div class="card-body p-20">
                <x-form id="save-agent-config-form" method="POST" action="{{ route('monitor.config.store') }}">
                    @include('monitor::config.partials.settings-fields')

                    <div class="alert alert-warning f-12 mb-4" role="alert">
                        <i class="fa fa-clock mr-1" aria-hidden="true"></i>
                        @lang('monitor::app.configRefreshNote')
                    </div>
                    <div class="border-top-grey pt-4">
                        <x-forms.button-primary id="save-agent-config" icon="check">
                            @lang('monitor::app.saveConfig')
                        </x-forms.button-primary>
                    </div>
                </x-form>
            </div>
        </div>

        <div class="card bg-white border-0 b-shadow-4 mb-0">
            <div class="card-header bg-white border-bottom-grey p-20">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <h4 class="f-16 f-w-500 mb-0">@lang('monitor::app.perEmployeeOverrides')</h4>
                        <p class="f-12 text-lightest mb-0 mt-1">@lang('monitor::app.perEmployeeOverridesHelp')</p>
                    </div>
                    <div class="col-md-4 text-md-right">
                        <x-forms.button-secondary id="add-config-override" icon="plus">
                            @lang('monitor::app.addOverride')
                        </x-forms.button-secondary>
                    </div>
                </div>
            </div>

            <div class="card-body monitor-search-panel p-20">
                @include('monitor::partials.table-search', [
                    'id' => 'monitor-config-search',
                    'placeholder' => __('monitor::app.searchEmployee'),
                ])
                <div class="table-responsive">
                    <table class="monitor-config-table monitor-searchable-table table table-hover w-100 f-14 mb-0">
                        <thead>
                            <tr class="text-uppercase f-11 text-lightest">
                                <th>@lang('app.employee')</th>
                                <th>@lang('monitor::app.settingsSummary')</th>
                                <th class="text-right">@lang('app.action')</th>
                            </tr>
                        </thead>
                        <tbody id="config-overrides-table">
                            @forelse ($overrides as $row)
                                <tr class="monitor-search-row"
                                    data-override-id="{{ $row['id'] }}"
                                    data-search="{{ strtolower($row['employee_name'] . ' ' . ($row['summary'] ?? '')) }}">
                                    <td class="f-w-500 text-darkest-grey">{{ $row['employee_name'] }}</td>
                                    <td class="f-12 text-dark-grey">{{ $row['summary'] }}</td>
                                    <td class="text-right">
                                        <a href="javascript:;"
                                            class="edit-override f-12 text-primary mr-2"
                                            data-override-id="{{ $row['id'] }}">@lang('app.edit')</a>
                                        <span class="text-lightest" aria-hidden="true">|</span>
                                        <a href="javascript:;"
                                            class="delete-override f-12 text-danger ml-2"
                                            data-override-id="{{ $row['id'] }}"
                                            data-override-url="{{ route('monitor.config.overrides.destroy', $row['id']) }}">@lang('app.remove')</a>
                                    </td>
                                </tr>
                            @empty
                                <tr id="config-overrides-empty">
                                    <td colspan="3" class="text-center py-5 f-14 text-lightest">
                                        @lang('monitor::app.noOverrides')
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
    <script>
        (function () {
            const swalButtonClasses = {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary',
            };

            const flaggedInput = document.getElementById('flagged_apps');
            if (flaggedInput && typeof Tagify !== 'undefined') {
                new Tagify(flaggedInput, { delimiters: ',', dropdown: { enabled: 0 } });
            }

            if (typeof window.initMonitorConfigForm === 'function') {
                window.initMonitorConfigForm($('#save-agent-config-form'));
            }

            $('#save-agent-config').on('click', function () {
                $.easyAjax({
                    url: "{{ route('monitor.config.store') }}",
                    container: '#save-agent-config-form',
                    type: 'POST',
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: '#save-agent-config',
                    data: $('#save-agent-config-form').serialize(),
                });
            });

            $('#add-config-override').on('click', function () {
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, "{{ route('monitor.config.overrides.create') }}");
            });

            $('body').off('click.monitorConfig', '.edit-override')
                .on('click.monitorConfig', '.edit-override', function () {
                    const id = $(this).data('override-id');
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, "{{ route('monitor.config.overrides.edit', ':id') }}".replace(':id', id));
                });

            $('body').off('click.monitorConfig', '.delete-override')
                .on('click.monitorConfig', '.delete-override', function () {
                    const url = $(this).data('override-url');

                    Swal.fire({
                        title: @json(__('messages.sweetAlertTitle')),
                        text: @json(__('messages.recoverRecord')),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: @json(__('messages.confirmDelete')),
                        cancelButtonText: @json(__('app.cancel')),
                        customClass: swalButtonClasses,
                        buttonsStyling: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.easyAjax({
                                url: url,
                                type: 'DELETE',
                                blockUI: true,
                                success: function () {
                                    window.location.reload();
                                },
                            });
                        }
                    });
                });

            if (typeof window.initializeDynamicUi === 'function') {
                window.initializeDynamicUi(document.querySelector('.content-wrapper'));
            }
        })();
    </script>
@endpush
