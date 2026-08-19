@if((user()->permission('manage_trainingpro_settings') == 'all' && in_array(\Modules\TrainingPro\Entities\TrainingProSetting::MODULE_NAME, user_modules())) || user()->is_superadmin)
	<x-setting-menu-item :active="$activeMenu" menu="trainingpro" :href="route('trainingpro-settings.index')" :text="__('trainingpro::app.menu.trainingproSetting')"/>
@endif
