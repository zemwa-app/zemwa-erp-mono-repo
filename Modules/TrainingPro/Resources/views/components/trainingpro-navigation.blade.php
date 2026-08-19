<div class="col-sm-12 rounded border border-grey bg-white py-4 text-right">
	@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()))
		<a class="btn btn-outline-secondary btn-sm" href="{{ route('trainingpro.index') }}"><i class="fa fa-home"></i></a>
		<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.results') }}"><i class="fa fa-check-square"></i> Assessment Summary</a>
		<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.home') }}"><i class="fa fa-cog"></i> Configuration</a>

    @endif
	@ if (in_array('trainingpro', user_modules()) && !in_array('admin', user_roles()))
	<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.trainings') }}"><i class="fa fa-tasks"></i> My Trainings</a>
	<a class="btn btn-outline-danger btn-sm active" href="{{ route('config.assessments') }}"><i class="fa fa-poll"></i> My Assessments</a>
	@ endif
</div>

