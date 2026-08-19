@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            @if(($restAPISetting->fcm_key || $restAPISetting->fcm_service_account_file) && user()->permission('manage_test_push_notification'))
                <x-slot name="buttons">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <x-forms.button-secondary icon="mobile" id="testAndroidPushNotification">
                                @lang('restapi::app.testAndroidPushNotification')
                            </x-forms.button-secondary>
                            <x-forms.button-secondary icon="mobile" id="testIOSPushNotification">
                                @lang('restapi::app.testIOSPushNotification')
                            </x-forms.button-secondary>
                        </div>
                    </div>
                </x-slot>
            @endif

            @if(user()->permission('manage_rest_api_settings'))
                <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                    @method('PUT')
                    <div class="row border rounded p-4 mx-0 bg-white">
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                <h5 class="mb-1">@lang('restapi::app.firebaseCloudMessaging')</h5>
                                @if(old('firebase_enabled', $restAPISetting->firebase_enabled ?? false))
                                    <span class="badge badge-success">@lang('app.enabled')</span>
                                @else
                                    <span class="badge badge-secondary">@lang('app.disabled')</span>
                                @endif
                            </div>
                            <p class="text-muted mb-4">@lang('restapi::app.firebaseCloudMessagingHelp')</p>

                            <div class="custom-control custom-checkbox mb-2">
                                <input type="hidden" name="firebase_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" name="firebase_enabled" id="firebaseEnabledUi"
                                       value="1"
                                       {{ old('firebase_enabled', $restAPISetting->firebase_enabled ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="firebaseEnabledUi">
                                    @lang('restapi::app.enableFirebaseNotifications')
                                </label>
                            </div>
                            <small class="text-muted d-block mb-3">@lang('restapi::app.enableFirebaseNotificationsHelp')</small>

                            <div class="border rounded p-3 mt-3 bg-light">
                                <x-forms.label fieldId="fcm_service_account_file" :fieldLabel="__('restapi::app.fcmServiceAccountJson')"></x-forms.label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" name="fcm_service_account_file" id="fcm_service_account_file" class="custom-file-input" accept=".json">
                                        <label class="custom-file-label" for="fcm_service_account_file" id="firebaseJsonFileLabel">
                                            @lang('restapi::app.chooseFile')
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">@lang('restapi::app.fcmServiceAccountJsonHelp')</small>
                            </div>

                            @if(!empty($restAPISetting->fcm_service_account_file))
                                <div class="border rounded p-3 mt-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div>
                                            <small class="text-success d-block">
                                                <i class="fa fa-check-circle"></i> @lang('restapi::app.fcmServiceAccountJsonUploaded')
                                            </small>
                                            <small class="text-muted d-block mt-1">
                                                @lang('restapi::app.currentFile'): <span class="font-weight-bold">{{ $firebaseJsonFileName }}</span>
                                            </small>
                                        </div>
                                        @if(env('APP_ENV') === 'codeanyon')
                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2 mt-md-0" id="toggleFirebaseJsonPreview"
                                                    data-url="{{ route('rest-api.firebase_json_preview') }}" data-loaded="0">
                                                @lang('restapi::app.seeFile')
                                            </button>
                                        @endif

                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-6 mt-4 mt-lg-0">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-3">@lang('restapi::app.fcmJsonStepsTitle')</h6>
                                <ol class="pl-3 mb-0 text-muted">
                                    <li class="mb-2"><span class="font-weight-bold">1.</span> @lang('restapi::app.fcmJsonStep1') <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank" rel="noopener">console.cloud.google.com</a></li>
                                    <li class="mb-2"><span class="font-weight-bold">2.</span> @lang('restapi::app.fcmJsonStep2')</li>
                                    <li class="mb-2"><span class="font-weight-bold">3.</span> @lang('restapi::app.fcmJsonStep3')</li>
                                    <li class="mb-2"><span class="font-weight-bold">4.</span> @lang('restapi::app.fcmJsonStep4')</li>
                                    <li class="mb-2"><span class="font-weight-bold">5.</span> @lang('restapi::app.fcmJsonStep5')</li>
                                    <li class="mb-2"><span class="font-weight-bold">6.</span> @lang('restapi::app.fcmJsonStep6')</li>
                                    <li class="mb-2"><span class="font-weight-bold">7.</span> @lang('restapi::app.fcmJsonStep7')</li>
                                    <li><span class="font-weight-bold">8.</span> @lang('restapi::app.fcmJsonStep8')</li>
                                </ol>
                            </div>
                        </div>

                        @if(!empty($restAPISetting->fcm_service_account_file))
                            <div class="col-12 mt-3">
                                <pre id="firebaseJsonPreview" class="p-3 border rounded bg-light d-none mb-0 w-100" style="max-width: 100%; max-height: 420px; overflow: auto; white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere;"></pre>
                            </div>
                        @endif
                    </div>

                </div>

                <x-slot name="action">
                    <!-- Buttons Start -->
                    <div class="w-100 border-top-grey">
                        <div class="settings-btns py-3 d-none d-lg-flex d-md-flex justify-content-end px-4">
                            <x-forms.button-cancel :link="url()->previous()" class="border-0 mr-3">@lang('app.cancel')
                            </x-forms.button-cancel>

                            <x-forms.button-primary id="save-form"
                                                    icon="check">@lang('app.save')</x-forms.button-primary>
                        </div>
                        <div class="d-block d-lg-none d-md-none p-4">
                            <div class="d-flex w-100">
                                <x-forms.button-primary class="mr-3 w-100" icon="check">@lang('app.save')
                                </x-forms.button-primary>
                            </div>
                            <x-forms.button-cancel :link="url()->previous()" class="w-100 mt-3">@lang('app.cancel')
                            </x-forms.button-cancel>
                        </div>
                    </div>
                    <!-- Buttons End -->
                </x-slot>
            @endif

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>

        $('#testAndroidPushNotification').click(function () {
            const url = "{{ route('rest-api.test_push', 'android') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#testIOSPushNotification').click(function () {
            const url = "{{ route('rest-api.test_push', 'ios') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#save-form').click(function () {
            const url = "{{ route('rest-api-setting.update', ['1']) }}";

            // easyAjax only sends multipart/FormData when file: true; otherwise it
            // forces processData and urlencoded contentType (breaks FormData → Illegal invocation).
            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                file: true,
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: {},
            })
        });

        $(document).on('click', '#toggleFirebaseJsonPreview', function () {
            const button = $(this);
            const preview = $('#firebaseJsonPreview');
            const isLoaded = button.attr('data-loaded') === '1';

            if (isLoaded) {
                preview.toggleClass('d-none');
                button.text(preview.hasClass('d-none') ? "{{ __('restapi::app.seeFile') }}" : "{{ __('restapi::app.hideFile') }}");
                return;
            }

            $.easyAjax({
                url: button.data('url'),
                type: "GET",
                container: '#editSettings',
                blockUI: true,
                success: function (response) {
                    if (response.status === 'success') {
                        preview.text(response.content || '');
                        preview.removeClass('d-none');
                        button.attr('data-loaded', '1');
                        button.text("{{ __('restapi::app.hideFile') }}");
                    }
                }
            });
        });

        $(document).on('change', '#fcm_service_account_file', function () {
            const fileName = this.files && this.files[0] ? this.files[0].name : "{{ __('restapi::app.chooseFile') }}";
            $('#firebaseJsonFileLabel').text(fileName);
        });

    </script>
@endpush
