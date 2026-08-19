@if (user()->is_superadmin && module_enabled('Aitools'))
    <x-setting-menu-item :active="$activeMenu" menu="ai_tools_settings" :href="route('ai-tools-settings.index')"
                         :text="__('aitools::app.aiTools')"/>
@endif
