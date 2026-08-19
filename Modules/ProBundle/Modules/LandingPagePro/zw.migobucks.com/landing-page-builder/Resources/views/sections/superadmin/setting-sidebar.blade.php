@if((user()->permission('manage_landingpagepro_settings') == 'all' && in_array(\Modules\LandingPagePro\Entities\LandingPageProSetting::MODULE_NAME, user_modules())) || user()->is_superadmin)
	<x-setting-menu-item :active="$activeMenu" menu="landingpagepro_settings" :href="route('landingpagepro-settings.index')" :text="__('landingpagepro::app.menu.landingpageproSetting')"/>
@endif
