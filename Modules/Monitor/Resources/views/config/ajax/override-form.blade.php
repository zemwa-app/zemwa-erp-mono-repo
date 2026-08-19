<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="w-100 p-20">
    <x-form id="save-override-form" method="POST"
        action="{{ $override ? route('monitor.config.overrides.update', $override->id) : route('monitor.config.overrides.store') }}">
        @if ($override)
            @method('PUT')
        @endif

        <div class="alert alert-info f-12 mb-4" role="alert">
            <i class="fa fa-info-circle mr-1" aria-hidden="true"></i>
            @lang('monitor::app.overrideFormIntro')
        </div>

        <div class="form-group mb-4">
            <x-forms.label fieldId="user_id" :fieldLabel="__('app.employee')" fieldRequired="true" />
            <p class="f-12 text-lightest mb-2">@lang('monitor::app.overrideEmployeeHelp')</p>
            <select name="user_id" id="user_id" class="form-control select-picker" data-live-search="true" data-size="8" data-container="body" @disabled(!!$override)>
                @if (!$override)
                    <option value="">@lang('monitor::app.selectEmployee')</option>
                @endif
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected($override && $override->user_id == $employee->id)>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
            @if ($override)
                <input type="hidden" name="user_id" value="{{ $override->user_id }}">
            @endif
        </div>

        @include('monitor::config.partials.settings-fields')

        <div class="d-flex justify-content-end border-top-grey pt-4 mt-4">
            <x-forms.button-secondary type="button" class="mr-2" data-dismiss="modal">@lang('app.cancel')</x-forms.button-secondary>
            <x-forms.button-primary id="save-override-btn" icon="check">@lang('app.save')</x-forms.button-primary>
        </div>
    </x-form>
</div>

<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
<script>
    (function () {
        const $form = $('#save-override-form');

        if (typeof window.initMonitorConfigForm !== 'function') {
            window.initMonitorConfigForm = function ($root) {
                const $scope = $root && $root.length ? $root : $(document);

                const syncDependencies = function () {
                    $scope.find('[data-depends-on]').each(function () {
                        const toggleId = $(this).data('depends-on');
                        const enabled = $scope.find('#' + toggleId).prop('checked');
                        $(this).toggleClass('monitor-config-disabled', !enabled);
                    });
                };

                $scope.find('#screenshot_enabled, #keyboard_enabled, #network_enabled').off('change.monitorConfig').on('change.monitorConfig', syncDependencies);
                syncDependencies();
            };
        }

        if (typeof refreshSelectPicker === 'function') {
            refreshSelectPicker('#save-override-form .select-picker');
        }

        const flaggedInput = $form.find('#flagged_apps').get(0);
        if (flaggedInput && typeof Tagify !== 'undefined' && !flaggedInput._tagify) {
            new Tagify(flaggedInput, { delimiters: ',', dropdown: { enabled: 0 } });
        }

        $('#save-override-btn').off('click.saveOverride').on('click.saveOverride', function () {
            $.easyAjax({
                url: $form.attr('action'),
                container: '#save-override-form',
                type: 'POST',
                disableButton: true,
                blockUI: true,
                buttonSelector: '#save-override-btn',
                data: $form.serialize(),
                success: function (response) {
                    if (response.status === 'success' && response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    }
                },
            });
        });

        window.initMonitorConfigForm($form);

        if (typeof init === 'function') {
            init(MODAL_LG);
        }
    })();
</script>
