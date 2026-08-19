@extends('layouts.app')

@section('content')

    @php
        $d = $downloadSetting;
        $hasMobile = filled($d->app_ios) || filled($d->app_android);
        $canManageUrls = user()->permission('manage_superadmin_app_settings') === 'all' ? true : false;
    @endphp

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card method="POST">
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                @if ($canManageUrls)
                    <div class="border rounded p-3 p-lg-4 mb-4">
                        <div class="row">
                            <div class="col-sm-12 mb-2">
                                <h4 class="mb-0 f-16 text-dark font-weight-normal">@lang('modules.accountSettings.downloadsStoreLinksSection')</h4>
                            </div>
                            <div class="col-sm-12 mb-4">
                                <p class="text-lightest f-14 mb-0">@lang('modules.accountSettings.partnerAppDownloadsHelp')</p>
                            </div>
                        </div>
                        @include('super-admin.download-settings.partials.mobile-urls-form')
                        <div class="row">
                            <div class="col-sm-12 mt-2">
                                <p class="f-12 text-lightest mb-0">@lang('modules.accountSettings.downloadsSaveHint')</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="border rounded p-3 p-lg-4 mb-4">
                        <div class="row">
                            <div class="col-sm-12 mb-4">
                                <p class="text-lightest f-14 mb-0">@lang('modules.accountSettings.downloadsReadOnlyHelp')</p>
                            </div>
                        </div>
                        @include('super-admin.download-settings.partials.mobile-urls-readonly', ['d' => $d, 'hasMobile' => $hasMobile])
                    </div>
                @endif

                <div class="row border-top-grey pt-4 mt-4">
                    <div class="col-sm-12">
                        <div class="card border-primary" style="background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);">
                            <div class="card-body p-3 p-lg-4">
                                <div class="position-relative">
                                    <span class="badge badge-primary position-absolute" style="top: 0; right: 0;">
                                        White-label
                                    </span>
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://envato.froid.works/logo-froiden.png" alt="Froiden" style="height: 32px;">
                                    <div class="ml-3">
                                        <h4 class="f-20 text-dark font-weight-bold mb-1">Order a Branded Mobile App</h4>
                                        <p class="text-muted mb-0">Get your own white-label app</p>
                                    </div>
                                </div>

                                <p class="text-muted f-16 mb-3">
                                    Want a mobile app with your branding? Order the white-label service and we will prepare it for your organization.
                                </p>

                                <div class="d-flex justify-content-start">
                                    <a href="https://envato.froid.works/my-account?tab=partner-mobile-app&utm_medium=download-settings&utm_campaign=mobile-app&purchase_code={{ global_setting()->purchase_code }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn btn-primary btn-sm py-2">
                                        <i class="fa fa-shopping-cart mr-1"></i>
                                        Order White-Label Mobile App
                                    </a>
                                </div>

                                <p class="text-muted small mb-0 mt-3">
                                    You will be redirected to Envato to place the order.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($canManageUrls)
                <x-slot name="action">
                    <div class="w-100 border-top-grey">
                        <x-setting-form-actions>
                            <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                            </x-forms.button-primary>
                        </x-setting-form-actions>
                    </div>
                </x-slot>
            @endif
        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        function updatePreviewDownloadButtons() {
            const iosUrl = ($('#app_ios').val() || '').trim();
            const androidUrl = ($('#app_android').val() || '').trim();
            const $iosBtn = $('#preview-ios-download-btn');
            const $androidBtn = $('#preview-android-download-btn');
            const $iosWrap = $('#preview-ios-download-wrap');
            const $androidWrap = $('#preview-android-download-wrap');

            if ($iosBtn.length) {
                $iosBtn.attr('href', iosUrl || 'javascript:;');
                $iosWrap.toggleClass('d-none', iosUrl === '');
            }

            if ($androidBtn.length) {
                $androidBtn.attr('href', androidUrl || 'javascript:;');
                $androidWrap.toggleClass('d-none', androidUrl === '');
            }
        }

        $('body').off('click', '.partner-clear-url').on('click', '.partner-clear-url', function () {
            const sel = $(this).data('target');
            $(sel).val('');
            updatePreviewDownloadButtons();
        });
        $('body').off('click', '.partner-reset-url').on('click', '.partner-reset-url', function () {
            const sel = $(this).data('target');
            const def = $(this).data('default');
            $(sel).val(def);
            updatePreviewDownloadButtons();
        });
        $('body').off('input', '#app_ios, #app_android').on('input', '#app_ios, #app_android', function () {
            updatePreviewDownloadButtons();
        });
        updatePreviewDownloadButtons();

        $('#save-form').click(function () {
            $.easyAjax({
                url: "{{ route('superadmin.settings.download-settings.update') }}",
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: {
                    _token: "{{ csrf_token() }}",
                    app_ios: $('#app_ios').val(),
                    app_android: $('#app_android').val(),
                },
            });
        });
    </script>
@endpush