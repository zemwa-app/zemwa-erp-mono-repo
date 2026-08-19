@if ( user()->permission('view_category') != 'none' && in_array(\Modules\TrainingPro\Entities\TrainingProSetting::MODULE_NAME, user_modules()) )
	{{--	{"all":4, "added":1, "owned":2,"both":3, "none":5}--}}
	<x-menu-item icon="training" :text="__('trainingpro::app.menu.trainingpro')">
		<x-slot name="iconPath">
			<path d="M3.302 12.238c.464 1.879 1.054 2.701 3.022 3.562 1.969.86 2.904 1.8 3.676 1.8.771 0 1.648-.822 3.616-1.684 1.969-.861 1.443-1.123 1.907-3.002L10 15.6l-6.698-3.362zm16.209-4.902l-8.325-4.662c-.652-.365-1.72-.365-2.372 0L.488 7.336c-.652.365-.652.963 0 1.328l8.325 4.662c.652.365 1.72.365 2.372 0l5.382-3.014-5.836-1.367a3.09 3.09 0 0 1-.731.086c-1.052 0-1.904-.506-1.904-1.131 0-.627.853-1.133 1.904-1.133.816 0 1.51.307 1.78.734l6.182 2.029 1.549-.867c.651-.364.651-.962 0-1.327zm-2.544 8.834c-.065.385 1.283 1.018 1.411-.107.579-5.072-.416-6.531-.416-6.531l-1.395.781c0-.001 1.183 1.125.4 5.857z"/>
		</x-slot>
		<div class="accordionItemContent pb-2">
			@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('trainingpro.index')" :text="__('trainingpro::app.menu.overview')"/>
			@endif

			@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()) && user()->permission('create_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('config.home')" :text="__('trainingpro::app.menu.config')"/>
			@endif

			@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('config.results')" :text="__('trainingpro::app.menu.result')"/>
			@endif

			@if (in_array('trainingpro', user_modules()) && !in_array('admin', user_roles()) && in_array('employee', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('config.trainings')" :text="__('trainingpro::app.menu.trainings')"/>
			@endif

			@if (in_array('trainingpro', user_modules()) && !in_array('admin', user_roles()) && in_array('employee', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('config.assessments')" :text="__('trainingpro::app.menu.assessments')"/>
			@endif
		</div>
	</x-menu-item>
@endif
{{--in_array('admin', user_roles()) && --}}
