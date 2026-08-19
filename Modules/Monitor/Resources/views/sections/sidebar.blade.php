@php
    $viewPermission = user()->permission('view_monitor');
@endphp

@if (module_enabled('Monitor') && !in_array('client', user_roles()) && $viewPermission != 'none' && in_array(\Modules\Monitor\Entities\MonitorSetting::MODULE_NAME, user_modules()))
    <x-menu-item icon="display" :text="__('monitor::app.monitorCenter')" :addon="App::environment('demo')">
        <x-slot name="iconPath">
            <path
                d="M0 4s0-2 2-2h12s2 0 2 2v6s0 2-2 2h-4c0 .667.083 1.167.25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75c.167-.333.25-.833.25-1.5H2s-2 0-2-2V4zm1.398-.855a.758.758 0 0 0-.254.302A1.46 1.46 0 0 0 1 4.01V10c0 .325.078.502.145.602.07.105.17.188.302.254a1.464 1.464 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.758.758 0 0 0 .254-.302 1.464 1.464 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.757.757 0 0 0-.302-.254A1.46 1.46 0 0 0 13.99 3H2c-.325 0-.502.078-.602.145z"/>
        </x-slot>

        <div class="accordionItemContent pb-2">
            <x-sub-menu-item :link="route('monitor.index')" :text="__('monitor::app.monitorCenter')"/>
            <x-sub-menu-item :link="route('monitor.analytics.index')" :text="__('monitor::app.analytics')"/>
            <x-sub-menu-item :link="route('monitor.screenshots.index')" :text="__('monitor::app.screenshots')"/>
            <x-sub-menu-item :link="route('monitor.reports.index')" :text="__('monitor::app.reports')"/>
            <x-sub-menu-item :link="route('monitor.config.index')"
                            :text="__('monitor::app.agentConfig')"
                            :permission="$viewPermission == 'all'"
            />
            <x-sub-menu-item :link="route('monitor.installer.index')" :text="__('monitor::app.downloadAgentInstaller')"/>
        </div>
    </x-menu-item>
@endif
