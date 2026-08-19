<div class="mb-4">
    <section class="card bg-white border-0 b-shadow-4 mb-4">
        <div class="card-header monitor-config-section-header p-20">
            <div class="d-flex align-items-start">
                <span class="monitor-setting-icon monitor-setting-icon--violet mr-3">
                    <i class="fa fa-camera"></i>
                </span>
                <div>
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">@lang('monitor::app.sectionScreenshots')</h4>
                    <p class="f-12 text-dark-grey mb-0 mt-1">@lang('monitor::app.sectionScreenshotsDesc')</p>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            @component('monitor::config.partials.setting-row', [
                'icon' => 'camera',
                'iconClass' => 'monitor-setting-icon--violet',
                'title' => __('monitor::app.screenshotCapture'),
                'description' => __('monitor::app.helpScreenshotCapture'),
            ])
                @include('monitor::config.partials.toggle-control', [
                    'id' => 'screenshot_enabled',
                    'name' => 'screenshot_enabled',
                    'checked' => $form['screenshot_enabled'],
                ])
            @endcomponent

            <div class="config-dependent" data-depends-on="screenshot_enabled">
                @component('monitor::config.partials.setting-row', [
                    'icon' => 'clock',
                    'iconClass' => 'monitor-setting-icon--grey',
                    'title' => __('monitor::app.screenshotInterval'),
                    'description' => __('monitor::app.helpScreenshotInterval'),
                    'nested' => true,
                ])
                    <select name="screenshot_interval" id="screenshot_interval"
                        class="form-control select-picker height-35" style="max-width: 220px;">
                        @foreach ($intervalOptions as $minutes)
                            <option value="{{ $minutes }}" @selected($form['screenshot_interval'] == $minutes)>
                                @lang('monitor::app.everyNMinutes', ['n' => $minutes])
                            </option>
                        @endforeach
                    </select>
                @endcomponent

                @component('monitor::config.partials.setting-row', [
                    'icon' => 'image',
                    'iconClass' => 'monitor-setting-icon--grey',
                    'title' => __('monitor::app.screenshotQuality'),
                    'description' => __('monitor::app.helpScreenshotQuality'),
                    'nested' => true,
                ])
                    <select name="screenshot_quality" id="screenshot_quality"
                        class="form-control select-picker height-35" style="max-width: 220px;">
                        <option value="low" @selected($form['screenshot_quality'] === 'low')>@lang('monitor::app.qualityLowLabel')</option>
                        <option value="medium" @selected($form['screenshot_quality'] === 'medium')>@lang('monitor::app.qualityMediumLabel')</option>
                        <option value="high" @selected($form['screenshot_quality'] === 'high')>@lang('monitor::app.qualityHighLabel')</option>
                    </select>
                @endcomponent

                @component('monitor::config.partials.setting-row', [
                    'icon' => 'pause-circle',
                    'iconClass' => 'monitor-setting-icon--grey',
                    'title' => __('monitor::app.pauseOnIdle'),
                    'description' => __('monitor::app.helpPauseOnIdle'),
                    'nested' => true,
                ])
                    @include('monitor::config.partials.toggle-control', [
                        'id' => 'screenshot_pause_on_idle',
                        'name' => 'screenshot_pause_on_idle',
                        'checked' => $form['screenshot_pause_on_idle'],
                    ])
                @endcomponent
            </div>
        </div>
    </section>

    <section class="card bg-white border-0 b-shadow-4 mb-4">
        <div class="card-header monitor-config-section-header p-20">
            <div class="d-flex align-items-start">
                <span class="monitor-setting-icon monitor-setting-icon--emerald mr-3">
                    <i class="fa fa-chart-line"></i>
                </span>
                <div>
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">@lang('monitor::app.sectionActivity')</h4>
                    <p class="f-12 text-dark-grey mb-0 mt-1">@lang('monitor::app.sectionActivityDesc')</p>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            @component('monitor::config.partials.setting-row', [
                'icon' => 'desktop',
                'iconClass' => 'monitor-setting-icon--emerald',
                'title' => __('monitor::app.appTracking'),
                'description' => __('monitor::app.helpAppTracking'),
            ])
                @include('monitor::config.partials.toggle-control', [
                    'id' => 'app_tracking_enabled',
                    'name' => 'app_tracking_enabled',
                    'checked' => $form['app_tracking_enabled'],
                ])
            @endcomponent

            @component('monitor::config.partials.setting-row', [
                'icon' => 'keyboard',
                'iconClass' => 'monitor-setting-icon--sky',
                'title' => __('monitor::app.keyboardMonitoring'),
                'description' => __('monitor::app.helpKeyboardMonitoring'),
            ])
                @include('monitor::config.partials.toggle-control', [
                    'id' => 'keyboard_enabled',
                    'name' => 'keyboard_enabled',
                    'checked' => $form['keyboard_enabled'],
                ])
            @endcomponent

            <div class="config-dependent" data-depends-on="keyboard_enabled">
                @component('monitor::config.partials.setting-row', [
                    'icon' => 'hourglass-half',
                    'iconClass' => 'monitor-setting-icon--grey',
                    'title' => __('monitor::app.idleThreshold'),
                    'description' => __('monitor::app.helpIdleThreshold'),
                    'nested' => true,
                ])
                    <select name="idle_threshold" id="idle_threshold"
                        class="form-control select-picker height-35" style="max-width: 220px;">
                        @foreach ($idleThresholdOptions as $minutes)
                            <option value="{{ $minutes }}" @selected($form['idle_threshold'] == $minutes)>
                                @lang('monitor::app.afterNMinutesIdle', ['n' => $minutes])
                            </option>
                        @endforeach
                    </select>
                @endcomponent
            </div>
        </div>
    </section>

    <section class="card bg-white border-0 b-shadow-4 mb-0">
        <div class="card-header monitor-config-section-header p-20">
            <div class="d-flex align-items-start">
                <span class="monitor-setting-icon monitor-setting-icon--amber mr-3">
                    <i class="fa fa-shield-alt"></i>
                </span>
                <div>
                    <h4 class="f-14 f-w-500 text-darkest-grey mb-0">@lang('monitor::app.sectionNetwork')</h4>
                    <p class="f-12 text-dark-grey mb-0 mt-1">@lang('monitor::app.sectionNetworkDesc')</p>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            @component('monitor::config.partials.setting-row', [
                'icon' => 'network-wired',
                'iconClass' => 'monitor-setting-icon--amber',
                'title' => __('monitor::app.networkMonitoring'),
                'description' => __('monitor::app.helpNetworkMonitoring'),
            ])
                @include('monitor::config.partials.toggle-control', [
                    'id' => 'network_enabled',
                    'name' => 'network_enabled',
                    'checked' => $form['network_enabled'],
                ])
            @endcomponent

            <div class="config-dependent" data-depends-on="network_enabled">
                @component('monitor::config.partials.setting-row', [
                    'icon' => 'cloud-upload-alt',
                    'iconClass' => 'monitor-setting-icon--grey',
                    'title' => __('monitor::app.largeUploadAlert'),
                    'description' => __('monitor::app.helpLargeUploadAlert'),
                    'nested' => true,
                ])
                    <div class="text-md-right">
                        <span class="d-inline-flex align-items-center">
                            <input type="number" name="large_transfer_mb" id="large_transfer_mb"
                                value="{{ $form['large_transfer_mb'] }}" min="1" max="1000"
                                class="form-control height-35 text-center mr-2" style="width: 96px;">
                            <span class="f-14 text-dark-grey">@lang('monitor::app.megabytes')</span>
                        </span>
                    </div>
                @endcomponent
            </div>

            @component('monitor::config.partials.setting-row', [
                'icon' => 'flag',
                'iconClass' => 'monitor-setting-icon--red',
                'title' => __('monitor::app.flaggedApps'),
                'description' => __('monitor::app.helpFlaggedApps'),
            ])
                <input type="text" name="flagged_apps" id="flagged_apps" value="{{ $form['flagged_apps'] }}"
                    class="tagify_tags form-control height-35"
                    style="max-width: 320px;"
                    placeholder="@lang('monitor::app.flaggedAppsPlaceholder')">
            @endcomponent
        </div>
    </section>
</div>

@once
    @push('scripts')
        <script>
            window.initMonitorConfigForm = function ($root) {
                const $scope = $root && $root.length ? $root : $(document);

                const syncDependencies = function () {
                    $scope.find('[data-depends-on]').each(function () {
                        const toggleId = $(this).data('depends-on');
                        const enabled = $scope.find('#' + toggleId).prop('checked');
                        $(this).toggleClass('monitor-config-disabled', !enabled);
                    });
                };

                $scope.find('#screenshot_enabled, #keyboard_enabled, #network_enabled').on('change', syncDependencies);
                syncDependencies();
            };
        </script>
    @endpush
@endonce
