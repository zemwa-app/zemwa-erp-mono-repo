@props(['platform', 'max_upload_mb'])

@php
    $sourceType = $platform['source_type'] ?? 'upload';
    $externalUrl = $platform['external_url'] ?? '';
    $configured = $platform['company_uploaded'] ?? $platform['uploaded'] ?? false;
@endphp

<div class="card bg-white border-0 b-shadow-4 mb-3 monitor-installer-file-card h-100"
    data-platform="{{ $platform['label'] }}"
    data-platform-key="{{ $platform['key'] }}"
    data-extension="{{ $platform['extension'] }}"
    data-installer-field="{{ $platform['key'] }}_installer"
    data-url-field="{{ $platform['key'] }}_download_url">
    <div class="card-body p-20">
        <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
                <p class="f-14 f-w-500 text-darkest-grey mb-1">{{ $platform['label'] }}</p>
                <p class="f-12 text-lightest mb-0">
                    @lang('monitor::app.installerAccepts', ['ext' => $platform['extension']])
                    · @lang('monitor::app.maxUploadSize', ['size' => $max_upload_mb . ' MB'])
                </p>
            </div>
            @if ($configured)
                <span class="badge badge-success f-12">
                    @if ($sourceType === 'url')
                        @lang('monitor::app.installerUrlConfigured')
                    @else
                        @lang('monitor::app.installerFileUploaded')
                    @endif
                </span>
            @endif
        </div>

        @if ($configured)
            <div class="bg-additional-grey rounded px-3 py-2 f-12 text-dark-grey mb-3">
                @if ($sourceType === 'url')
                    <p class="f-w-500 text-darkest-grey mb-1">@lang('monitor::app.installerExternalLink')</p>
                    <p class="mb-0 text-break">{{ $externalUrl }}</p>
                @else
                    <p class="f-w-500 text-darkest-grey mb-1">{{ $platform['filename'] }}</p>
                    <p class="mb-0">
                        {{ $platform['size_label'] }}
                    </p>
                @endif
                @if ($platform['uploaded_at'])
                    <p class="mb-0 mt-1">
                        @if ($sourceType === 'url')
                            @lang('monitor::app.urlConfiguredAt', ['date' => $platform['uploaded_at']])
                        @else
                            @lang('monitor::app.uploadedAt', ['date' => $platform['uploaded_at']])
                        @endif
                    </p>
                @endif
            </div>
            @if (!empty($platform['destroy_url']))
                <button type="button"
                    class="btn btn-link text-danger p-0 mb-3 remove-installer f-12"
                    data-url="{{ $platform['destroy_url'] }}"
                    data-label="{{ $platform['label'] }}">
                    <i class="fa fa-trash-alt mr-1"></i>
                    @if ($sourceType === 'url')
                        @lang('monitor::app.removeInstallerUrl')
                    @else
                        @lang('monitor::app.removeInstaller')
                    @endif
                </button>
            @endif
        @endif

        <div class="monitor-installer-source-tabs mb-0">
            <p class="f-12 text-lightest mb-2">@lang('monitor::app.installerDeliveryMethod')</p>
            <nav class="tabs border-bottom-grey">
                <div class="nav" role="tablist">
                    <label class="nav-item nav-link f-13 monitor-installer-tab {{ $sourceType === 'upload' ? 'active' : '' }}">
                        <input type="radio"
                            name="{{ $platform['key'] }}_source_type"
                            value="upload"
                            class="monitor-installer-source-input"
                            autocomplete="off"
                            {{ $sourceType === 'upload' ? 'checked' : '' }}>
                        <i class="fa fa-upload mr-1"></i>@lang('monitor::app.installerSourceUpload')
                    </label>
                    <label class="nav-item nav-link f-13 monitor-installer-tab {{ $sourceType === 'url' ? 'active' : '' }}">
                        <input type="radio"
                            name="{{ $platform['key'] }}_source_type"
                            value="url"
                            class="monitor-installer-source-input"
                            autocomplete="off"
                            {{ $sourceType === 'url' ? 'checked' : '' }}>
                        <i class="fa fa-link mr-1"></i>@lang('monitor::app.installerSourceUrl')
                    </label>
                </div>
            </nav>

            <div class="monitor-installer-tab-panel border border-top-0 rounded-bottom p-3 mb-2">
                <div class="monitor-installer-upload-panel" style="{{ $sourceType === 'url' ? 'display:none;' : '' }}">
                    <div class="form-group mb-2">
                        <label class="f-12 text-dark-grey mb-1" for="installer-{{ $platform['key'] }}">
                            {{ $configured && $sourceType === 'upload' ? __('monitor::app.replaceInstaller') : __('monitor::app.chooseInstaller') }}
                        </label>
                        <input type="file"
                            name="{{ $platform['key'] }}_installer"
                            id="installer-{{ $platform['key'] }}"
                            accept=".{{ $platform['extension'] }}"
                            class="form-control-file f-14 monitor-installer-file-input">
                    </div>

                    <div class="monitor-installer-file-selected bg-white rounded px-3 py-2 f-12 text-dark-grey mb-0" style="display: none;"></div>
                </div>

                <div class="monitor-installer-url-panel" style="{{ $sourceType === 'upload' ? 'display:none;' : '' }}">
                    <div class="form-group mb-0">
                        <x-forms.text
                            :fieldLabel="__('monitor::app.installerDownloadUrlLabel')"
                            :fieldName="$platform['key'] . '_download_url'"
                            :fieldId="'installer-url-' . $platform['key']"
                            :fieldValue="$externalUrl"
                            :fieldPlaceholder="__('monitor::app.installerDownloadUrlPlaceholder')"
                            :fieldHelp="__('monitor::app.installerDownloadUrlHelp', ['ext' => $platform['extension']])"
                        />
                    </div>
                </div>
            </div>
        </div>

        <p class="monitor-installer-file-error text-danger f-12 mb-0" style="display: none;"></p>
    </div>
</div>
