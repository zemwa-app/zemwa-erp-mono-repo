@if (in_array('aitools', user_modules()) && module_enabled('Aitools') && user()->permission('edit_aitools') == 'all')
    <x-setting-menu-item :active="$activeMenu" menu="ai_tools_usage" :href="route('ai-tools-usage.index')"
                         :text="__('aitools::app.usageHistory')"/>
@endif
