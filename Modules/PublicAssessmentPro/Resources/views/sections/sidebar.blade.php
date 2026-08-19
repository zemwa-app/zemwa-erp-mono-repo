@if ( user()->permission('view_category') != 'none' && in_array(\Modules\PublicAssessmentPro\Entities\PublicAssessmentProSetting::MODULE_NAME, user_modules()) )
	{{--	{"all":4, "added":1, "owned":2,"both":3, "none":5}--}}
	<x-menu-item icon="assessment" :text="__('publicassessmentpro::app.menu.publicassessmentpro')">
		<x-slot name="iconPath">

		<path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-2 14l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>

		</x-slot>
		<div class="accordionItemContent pb-2">
			@if (in_array('publicassessmentpro', user_modules()) && in_array('admin', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('publicassessmentpro.index')" :text="__('publicassessmentpro::app.menu.overview')"/>
			@endif

			@if (in_array('publicassessmentpro', user_modules()) && in_array('admin', user_roles()) && user()->permission('create_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('publicassessmentpro.config.home')" :text="__('publicassessmentpro::app.menu.config')"/>
			@endif

			@if (in_array('publicassessmentpro', user_modules()) && in_array('admin', user_roles()) && user()->permission('view_category') != 5 && user()->permission('view_category') != 'none')
				<x-sub-menu-item :link="route('publicassessmentpro.config.participants')" :text="__('publicassessmentpro::app.menu.participants')"/>
			@endif

		</div>
	</x-menu-item>
@endif
{{--in_array('admin', user_roles()) && --}}
