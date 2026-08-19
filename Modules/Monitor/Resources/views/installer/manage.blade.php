@extends('layouts.app')

@push('styles')
    @include('monitor::partials.styles')
@endpush

@section('content')
    <div class="w-100 d-flex">
        <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang($pageTitle)
                    </h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">

                <div class="card border-primary mb-4"
                style="background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);">
                <div class="card-body p-3 p-lg-4">
                    <div class="position-relative">
                        <span class="badge badge-primary position-absolute" style="top: 0; right: 0;">
                            @lang('monitor::app.whiteLabel')
                        </span>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div>
                            <h4 class="f-20 text-dark font-weight-bold mb-1">
                                @lang('monitor::app.orderAppBuild')
                            </h4>
                            <p class="text-muted mb-0">@lang('monitor::app.orderAppBuildSubtitle')</p>
                        </div>
                    </div>

                    <p class="text-muted f-16 mb-3">
                        @lang('monitor::app.orderAppBuildIntro')
                    </p>

                    <div class="d-flex justify-content-start">
                        <a href="https://envato.froid.works/my-account?tab=time-tracker-app&utm_medium=monitor-installer&utm_campaign=time-tracker-app&purchase_code={{ global_setting()->purchase_code }}"
                            target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm py-2">
                            <i class="fa fa-shopping-cart mr-1"></i>
                            @lang('monitor::app.orderAppBuildCta')
                        </a>
                    </div>

                    <p class="text-muted small mb-0 mt-3">
                        @lang('monitor::app.orderAppBuildRedirectNote')
                    </p>
                </div>
            </div>

                @if ($can_manage && !App::environment('demo'))
                    <x-cards.data :title="__('monitor::app.manageInstallers')" class="mb-4">
                        <p class="f-14 text-dark-grey mb-4">
                            @lang('monitor::app.manageInstallersIntro')
                        </p>

                        <div id="upload-installer-form">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-forms.text
                                        :fieldLabel="__('monitor::app.agentVersionLabel')"
                                        fieldName="agent_version"
                                        fieldId="agent_version"
                                        :fieldValue="$version"
                                        :fieldPlaceholder="__('monitor::app.agentVersionPlaceholder')"
                                        :fieldHelp="__('monitor::app.agentVersionHelp')"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.datepicker
                                        :fieldLabel="__('monitor::app.releaseDateLabel')"
                                        fieldName="released_at"
                                        fieldId="released_at"
                                        fieldPlaceholder=""
                                        :fieldValue="$released_at"
                                        :fieldHelp="__('monitor::app.releaseDateHelp')"
                                    />
                                </div>
                            </div>

                            <div id="installer-upload-alert" class="mb-3" style="display: none;"></div>

                            <div class="row">
                                @foreach ($platforms as $platform)
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        @include('monitor::installer.partials.platform-upload', [
                                            'platform' => $platform,
                                            'max_upload_mb' => $max_upload_mb,
                                        ])
                                    </div>
                                @endforeach
                            </div>

                            <div id="installer-upload-progress" class="monitor-installer-upload-progress mb-3" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="f-14 f-w-500 text-darkest-grey" id="installer-upload-status">
                                        @lang('monitor::app.installerUploadPreparing')
                                    </span>
                                    <span class="f-14 text-primary f-w-500" id="installer-upload-percent">0%</span>
                                </div>
                                <div class="progress monitor-installer-progress">
                                    <div id="installer-upload-bar"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar"
                                        style="width: 0%;"
                                        aria-valuenow="0"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <p class="f-12 text-lightest mb-0 mt-2" id="installer-upload-hint">
                                    @lang('monitor::app.installerUploadLargeFileHint', ['size' => $max_upload_mb . ' MB'])
                                </p>
                            </div>

                            <div class="border-top-grey pt-4 mt-2">
                                <x-forms.button-primary id="upload-installer-btn" icon="check">
                                    @lang('monitor::app.saveInstallers')
                                </x-forms.button-primary>
                                <p class="f-12 text-lightest mb-0 mt-2">@lang('monitor::app.saveInstallersHelp')</p>
                            </div>
                        </div>
                    </x-cards.data>

                @else
                    <div class="alert alert-warning mb-0">
                        @lang('monitor::app.installerManageUnavailable')
                    </div>
                @endif
            </div>
        </x-setting-card>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const swalButtonClasses = {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary',
            };

            @if ($can_manage)
            const maxUploadBytes = {{ (int) $max_upload_mb * 1024 * 1024 }};
            const maxUploadLabel = @json($max_upload_mb . ' MB');
            const uploadUrl = @json(route('monitor.installer-settings.upload'));
            const csrfToken = @json(csrf_token());
            const originalVersion = @json($version);
            const originalReleasedAt = @json($released_at);
            const originalUrls = @json(collect($platforms)->mapWithKeys(fn ($p) => [$p['key'] => $p['external_url'] ?? '']));
            const i18n = {
                preparing: @json(__('monitor::app.installerUploadPreparing')),
                uploading: @json(__('monitor::app.installerUploading')),
                processing: @json(__('monitor::app.installerUploadProcessing')),
                complete: @json(__('monitor::app.installerUploadComplete')),
                fileTooLarge: @json(__('monitor::app.installerFileTooLarge', ['size' => '__SIZE__'])),
                invalidExtension: @json(__('monitor::app.installerInvalidExtensionClient', ['platform' => '__PLATFORM__', 'extension' => '__EXT__'])),
                invalidUrl: @json(__('monitor::app.installerInvalidUrl', ['platform' => '__PLATFORM__'])),
                invalidUrlExtension: @json(__('monitor::app.installerInvalidUrlExtension', ['platform' => '__PLATFORM__', 'extension' => '__EXT__'])),
                connectionTimeout: @json(__('monitor::app.installerUploadTimeout')),
                serverError: @json(__('monitor::app.installerUploadServerError')),
                payloadTooLarge: @json(__('monitor::app.installerUploadPayloadTooLarge', ['size' => '__SIZE__'])),
                networkError: @json(__('monitor::app.installerUploadNetworkError')),
                nothingSelected: @json(__('monitor::app.installerUploadNothing')),
                selectedFile: @json(__('monitor::app.installerSelectedFile')),
                fixErrors: @json(__('monitor::app.installerUploadFixErrors')),
            };

            function isValidDownloadUrl(value) {
                try {
                    const url = new URL(value);
                    return url.protocol === 'http:' || url.protocol === 'https:';
                } catch (e) {
                    return false;
                }
            }

            function getUrlExtension(value) {
                try {
                    const parts = new URL(value).pathname.split('.');
                    return parts.length > 1 ? parts.pop().toLowerCase() : '';
                } catch (e) {
                    return '';
                }
            }

            function formatFileSize(bytes) {
                if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(1) + ' MB';
                }
                if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(1) + ' KB';
                }
                return bytes + ' B';
            }

            function showUploadAlert(message, type) {
                const typeClass = type === 'success' ? 'success' : 'danger';
                $('#installer-upload-alert')
                    .html('<div class="alert alert-' + typeClass + ' mb-0 f-14">' + message + '</div>')
                    .show();
            }

            function hideUploadAlert() {
                $('#installer-upload-alert').hide().html('');
            }

            function clearPlatformErrors() {
                $('.monitor-installer-file-error').hide().text('');
                $('.monitor-installer-file-card').removeClass('border-danger');
            }

            function setUploadProgress(percent, statusText) {
                const safePercent = Math.min(100, Math.max(0, Math.round(percent)));
                $('#installer-upload-progress').show();
                $('#installer-upload-bar')
                    .css('width', safePercent + '%')
                    .attr('aria-valuenow', safePercent);
                $('#installer-upload-percent').text(safePercent + '%');
                if (statusText) {
                    $('#installer-upload-status').text(statusText);
                }
            }

            function resetUploadProgress() {
                $('#installer-upload-progress').hide();
                setUploadProgress(0, i18n.preparing);
            }

            function setButtonLoading(isLoading) {
                const $btn = $('#upload-installer-btn');
                if (isLoading) {
                    $btn.prop('disabled', true).addClass('disabled');
                } else {
                    $btn.prop('disabled', false).removeClass('disabled');
                }
            }

            function handleUploadFail(response) {
                if (response && response.message) {
                    showUploadAlert(response.message, 'error');
                }

                if (response && response.errors) {
                    Object.keys(response.errors).forEach(function (field) {
                        const message = response.errors[field][0];
                        const $card = $('[data-installer-field="' + field + '"], [data-url-field="' + field + '"]').first();
                        if ($card.length) {
                            $card.addClass('border-danger');
                            $card.find('.monitor-installer-file-error').text(message).show();
                        } else {
                            showUploadAlert(message, 'error');
                        }
                    });
                }
            }

            function parseUploadError(jqXHR, textStatus) {
                if (textStatus === 'timeout') {
                    return i18n.connectionTimeout;
                }

                if (jqXHR.status === 413) {
                    return i18n.payloadTooLarge.replace('__SIZE__', maxUploadLabel);
                }

                try {
                    const response = JSON.parse(jqXHR.responseText);
                    if (response.message) {
                        return response.message;
                    }
                    if (response.errors) {
                        handleUploadFail(response);
                        return null;
                    }
                } catch (e) {
                    // Non-JSON response (e.g. nginx/html error page)
                }

                if (jqXHR.status >= 500) {
                    return i18n.serverError;
                }

                if (jqXHR.status === 0) {
                    return i18n.networkError;
                }

                return i18n.serverError;
            }

            $('.monitor-installer-file-input').on('change', function () {
                const $input = $(this);
                const $card = $input.closest('.monitor-installer-file-card');
                const platform = $card.data('platform');
                const expectedExt = String($card.data('extension') || '').toLowerCase();
                const $selected = $card.find('.monitor-installer-file-selected');
                const $error = $card.find('.monitor-installer-file-error');
                const file = this.files && this.files[0] ? this.files[0] : null;

                $card.removeClass('border-danger');
                $error.hide().text('');

                if (!file) {
                    $selected.hide().html('');
                    return;
                }

                const fileExt = file.name.split('.').pop().toLowerCase();
                if (expectedExt && fileExt !== expectedExt) {
                    $input.val('');
                    $selected.hide().html('');
                    $error.text(
                        i18n.invalidExtension
                            .replace('__PLATFORM__', platform)
                            .replace('__EXT__', expectedExt)
                    ).show();
                    $card.addClass('border-danger');
                    return;
                }

                if (file.size > maxUploadBytes) {
                    $input.val('');
                    $selected.hide().html('');
                    $error.text(i18n.fileTooLarge.replace('__SIZE__', maxUploadLabel)).show();
                    $card.addClass('border-danger');
                    return;
                }

                $selected.html(
                    '<p class="f-w-500 text-darkest-grey mb-1">' + file.name + '</p>' +
                    '<p class="mb-0">' + i18n.selectedFile.replace(':size', formatFileSize(file.size)) + '</p>'
                ).show();
            });

            $('body').on('change', '.monitor-installer-source-input', function () {
                const $card = $(this).closest('.monitor-installer-file-card');
                const source = $(this).val();

                $card.find('.monitor-installer-tab').removeClass('active');
                $(this).closest('.monitor-installer-tab').addClass('active');

                if (source === 'url') {
                    $card.find('.monitor-installer-upload-panel').hide();
                    $card.find('.monitor-installer-url-panel').show();
                    $card.find('.monitor-installer-file-input').val('');
                    $card.find('.monitor-installer-file-selected').hide().html('');
                } else {
                    $card.find('.monitor-installer-upload-panel').show();
                    $card.find('.monitor-installer-url-panel').hide();
                    $card.find('input[name="' + $card.data('platformKey') + '_download_url"]').val('');
                }

                $card.removeClass('border-danger');
                $card.find('.monitor-installer-file-error').hide().text('');
            });

            function buildInstallerFormData() {
                const formData = new FormData();

                $('.monitor-installer-file-card').each(function () {
                    const $card = $(this);
                    const platformKey = $card.data('platformKey');
                    const source = $card.find('.monitor-installer-source-input:checked').val() || 'upload';

                    formData.append(platformKey + '_source_type', source);

                    if (source === 'upload') {
                        const $fileInput = $card.find('.monitor-installer-file-input').get(0);
                        if ($fileInput && $fileInput.files && $fileInput.files.length) {
                            formData.append($fileInput.name, $fileInput.files[0]);
                        }
                        return;
                    }

                    const urlValue = $.trim($card.find('input[name="' + platformKey + '_download_url"]').val());
                    if (urlValue !== '') {
                        formData.append(platformKey + '_download_url', urlValue);
                    }
                });

                formData.append('agent_version', $.trim($('#agent_version').val()));
                formData.append('released_at', $.trim($('#released_at').val()));
                formData.append('_token', csrfToken);

                return formData;
            }

            $('#upload-installer-btn').on('click', function (e) {
                e.preventDefault();
                hideUploadAlert();
                clearPlatformErrors();

                const formData = buildInstallerFormData();
                let hasFile = false;
                let hasUrlUpdate = false;
                let hasValidationError = false;

                $('.monitor-installer-file-input').each(function () {
                    const $card = $(this).closest('.monitor-installer-file-card');
                    const source = $card.find('.monitor-installer-source-input:checked').val();

                    if (source !== 'upload' || !this.files || !this.files.length) {
                        return;
                    }

                    hasFile = true;
                    const file = this.files[0];
                    const expectedExt = String($card.data('extension') || '').toLowerCase();
                    const fileExt = file.name.split('.').pop().toLowerCase();

                    if (expectedExt && fileExt !== expectedExt) {
                        hasValidationError = true;
                        $card.addClass('border-danger');
                        $card.find('.monitor-installer-file-error').text(
                            i18n.invalidExtension
                                .replace('__PLATFORM__', $card.data('platform'))
                                .replace('__EXT__', expectedExt)
                        ).show();
                    } else if (file.size > maxUploadBytes) {
                        hasValidationError = true;
                        $card.addClass('border-danger');
                        $card.find('.monitor-installer-file-error')
                            .text(i18n.fileTooLarge.replace('__SIZE__', maxUploadLabel))
                            .show();
                    }
                });

                $('.monitor-installer-file-card').each(function () {
                    const $card = $(this);
                    const platformKey = $card.data('platformKey');
                    const platformLabel = $card.data('platform');
                    const expectedExt = String($card.data('extension') || '').toLowerCase();
                    const source = $card.find('.monitor-installer-source-input:checked').val();

                    if (source !== 'url') {
                        return;
                    }

                    const urlValue = $.trim($card.find('input[name="' + platformKey + '_download_url"]').val());
                    const originalUrl = String(originalUrls[platformKey] || '');

                    if (!urlValue || urlValue === originalUrl) {
                        return;
                    }

                    hasUrlUpdate = true;

                    if (!isValidDownloadUrl(urlValue)) {
                        hasValidationError = true;
                        $card.addClass('border-danger');
                        $card.find('.monitor-installer-file-error')
                            .text(i18n.invalidUrl.replace('__PLATFORM__', platformLabel))
                            .show();
                        return;
                    }

                    const urlExt = getUrlExtension(urlValue);
                    if (expectedExt && urlExt && urlExt !== expectedExt) {
                        hasValidationError = true;
                        $card.addClass('border-danger');
                        $card.find('.monitor-installer-file-error')
                            .text(i18n.invalidUrlExtension
                                .replace('__PLATFORM__', platformLabel)
                                .replace('__EXT__', expectedExt))
                            .show();
                    }
                });

                const hasMetaUpdate = $.trim($('#agent_version').val()) !== String(originalVersion || '')
                    || $.trim($('#released_at').val()) !== String(originalReleasedAt || '');

                if (!hasFile && !hasUrlUpdate && !hasMetaUpdate) {
                    showUploadAlert(i18n.nothingSelected, 'error');
                    return;
                }

                if (hasValidationError) {
                    showUploadAlert(i18n.fixErrors, 'error');
                    return;
                }

                setButtonLoading(true);
                setUploadProgress(0, hasFile ? i18n.uploading : i18n.processing);

                if (!hasFile) {
                    $('#installer-upload-hint').text(@json(__('monitor::app.installerUploadProcessing')));
                }

                $.ajax({
                    url: uploadUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    timeout: 0,
                    xhr: function () {
                        const xhr = $.ajaxSettings.xhr();
                        if (xhr.upload && hasFile) {
                            xhr.upload.addEventListener('progress', function (event) {
                                if (event.lengthComputable) {
                                    const percent = (event.loaded / event.total) * 100;
                                    setUploadProgress(percent, i18n.uploading + ' (' + formatFileSize(event.loaded) + ' / ' + formatFileSize(event.total) + ')');
                                } else {
                                    setUploadProgress(0, i18n.uploading);
                                }
                            }, false);
                        }
                        return xhr;
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            setUploadProgress(100, i18n.complete);
                            if (response.redirectUrl) {
                                window.location.href = response.redirectUrl;
                                return;
                            }
                            window.location.reload();
                            return;
                        }

                        if (response.status === 'fail') {
                            handleUploadFail(response);
                        }
                    },
                    error: function (jqXHR, textStatus) {
                        const message = parseUploadError(jqXHR, textStatus);
                        if (message) {
                            showUploadAlert(message, 'error');
                        }
                    },
                    complete: function () {
                        setButtonLoading(false);
                        if (!$('#installer-upload-bar').attr('aria-valuenow') || $('#installer-upload-bar').attr('aria-valuenow') === '100') {
                            return;
                        }
                        resetUploadProgress();
                    },
                });
            });

            $('body').off('click.monitorInstaller', '.remove-installer')
                .on('click.monitorInstaller', '.remove-installer', function () {
                    const url = $(this).data('url');
                    const label = $(this).data('label');

                    Swal.fire({
                        title: @json(__('messages.sweetAlertTitle')),
                        text: @json(__('monitor::app.installerRemoveConfirm', ['platform' => '__LABEL__'])).replace('__LABEL__', label),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: @json(__('app.remove')),
                        cancelButtonText: @json(__('app.cancel')),
                        customClass: swalButtonClasses,
                        buttonsStyling: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: csrfToken,
                                },
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                beforeSend: function () {
                                    $.easyBlockUI('body');
                                },
                                complete: function () {
                                    $.easyUnblockUI('body');
                                },
                                success: function (response) {
                                    if (response.status === 'success') {
                                        window.location.reload();
                                    } else if (response.message) {
                                        Swal.fire({
                                            icon: 'error',
                                            text: response.message,
                                            customClass: swalButtonClasses,
                                            buttonsStyling: false,
                                        });
                                    }
                                },
                                error: function (jqXHR) {
                                    let message = @json(__('messages.errorOccured'));
                                    try {
                                        const response = JSON.parse(jqXHR.responseText);
                                        if (response.message) {
                                            message = response.message;
                                        }
                                    } catch (e) {
                                        // keep default message
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        text: message,
                                        customClass: swalButtonClasses,
                                        buttonsStyling: false,
                                    });
                                },
                            });
                        }
                    });
                });
            @endif
        })();
    </script>
@endpush
