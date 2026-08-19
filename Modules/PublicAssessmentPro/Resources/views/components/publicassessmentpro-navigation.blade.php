<div class="container-fluid">
			<div class="col-sm-12 rounded border border-grey bg-white py-4 text-right">
				@if (in_array('publicassessmentpro', user_modules()) && in_array('admin', user_roles()))
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('publicassessmentpro.index') }}"><i class="fa fa-home"></i></a>
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('publicassessmentpro.config.home') }}"><i class="fa fa-cog"></i> Configuration</a>
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('publicassessmentpro.config.participants') }}"><i class="fa fa-check-square"></i> Assessment Participants</a>
					@endif
				@if (in_array('publicassessmentpro', user_modules()) && !in_array('admin', user_roles()))
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('publicassessmentpro.index') }}"><i class="fa fa-home"></i></a>
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('publicassessmentpro.config.participants') }}"><i class="fa fa-check-square"></i> Assessment Participants</a>
				@endif
			</div>
	</div>

