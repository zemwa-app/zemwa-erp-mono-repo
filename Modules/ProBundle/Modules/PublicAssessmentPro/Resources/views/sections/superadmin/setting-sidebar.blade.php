@if((user()->permission('manage_publicassessmentpro_settings') == 'all' && in_array(\Modules\PublicAssessmentPro\Entities\PublicAssessmentProSetting::MODULE_NAME, user_modules())) || user()->is_superadmin)
	<x-setting-menu-item :active="$activeMenu" menu="publicassessmentpro_settings" :href="route('publicassessmentpro-settings.index')" :text="__('publicassessmentpro::app.menu.publicassessmentproSetting')"/>
@endif
