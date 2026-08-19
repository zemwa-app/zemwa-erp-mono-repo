@if((user()->permission('manage_leadformspro_settings') == 'all' && in_array(\Modules\LeadFormsPro\Entities\LeadFormsProSetting::MODULE_NAME, user_modules())) || user()->is_superadmin)
	<x-setting-menu-item :active="$activeMenu" menu="leadformspro_settings" :href="route('leadformspro-settings.index')" :text="__('leadformspro::app.menu.leadformsproSetting')"/>
@endif
