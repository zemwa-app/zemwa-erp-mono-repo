@extends('layouts.app')

@push('datatable-styles')

@endpush

@php
	$companyId = company()->id;
	//dd( in_array('trainingpro', user_modules()) );
@endphp

@section('content')
	<div class="container-fluid">
		<div class="row p-3 justify-content-around">
			<div class="col-sm-12 rounded border border-grey bg-white py-4 text-right">
				@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()))
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('trainingpro.index') }}"><i class="fa fa-home"></i></a>
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.results') }}"><i class="fa fa-check-square"></i> Assessment Summary</a>
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.home') }}"><i class="fa fa-cog"></i> Configuration</a>
				@endif
				@if (in_array('trainingpro', user_modules()) && !in_array('admin', user_roles()))
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.trainings') }}"><i class="fa fa-tasks"></i> My Trainings</a>
					<a class="btn btn-outline-danger btn-sm active" href="{{ route('config.assessments') }}"><i class="fa fa-poll"></i> My Assessments</a>
				@endif
			</div>
		</div>
	</div>

	<!-- INFOBOX WRAPPER START -->
	<div class="container-fluid">
		<div class="row p-3 justify-content-around">
			<div class="col-sm-6 col-md-3 rounded border border-grey bg-white py-4">
				<div class="text-center text-primary">
					<div class="rounded-circle mb-3">
						<span class="fas fa-graduation-cap fa-3x"></span>
					</div>
					<h3 class="h3 mb-0">{{ count($userTrainings) ?: 0 }}</h3>
					<p class="f-12 text-secondary text-uppercase">{{ count($userTrainings) > 0 ? (count($userTrainings) <= 1 ? 'Programme' : 'Programmes') : 0 }}</p>
				</div>
			</div>
			<div class="col-sm-6 col-md-3 rounded border border-grey bg-white py-4">
				<div class="text-center text-primary">
					<div class="rounded-circle mb-3">
						<span class="fas fa-tasks fa-3x"></span>
					</div>
					<h3 class="h3 mb-0">{{ $totalTopics ?: 0 }}</h3>
					<p class="f-12 text-secondary text-uppercase">{{ $totalTopics > 0 ? ($totalTopics <= 1 ? 'Topic' : 'Topics') : 0 }}</p>
				</div>
			</div>
			<div class="col-sm-6 col-md-3 rounded border border-grey bg-white py-4">
				<div class="text-center text-primary">
					<div class="rounded-circle mb-3">
						<span class="fas fa-tasks fa-3x"></span>
					</div>
					<h3 class="h3 mb-0">{{ $totalAssessments ?: 0 }}</h3>
					<p class="f-12 text-secondary text-uppercase">{{ $totalAssessments > 0 ? ($totalAssessments <= 1 ? 'Assessment' : 'Assessments') : 0 }}</p>
				</div>
			</div>
			<div class="col-sm-6 col-md-3 rounded border border-grey bg-white py-4">
				<div class="text-center text-primary">
					<div class="rounded-circle mb-3">
						<span class="fas fa-shield-alt fa-3x"></span>
					</div>
					<h3 class="h3 mb-0">{{ count($completedAssessments) ?: 0 }} </h3>
					<p class="f-12 text-secondary text-uppercase">Assessments Completed</p>
				</div>
			</div>
		</div>
	</div>
	<!-- INFOBOX WRAPPER END -->

	<!-- INFOBOX WRAPPER START -->
	<div class="container-fluid">
		<div class="row p-3">
			<div class="col-sm-12 rounded border border-grey bg-white py-4">
				<div class="col-sm-12 mb-2 pb-2 border-bottom-grey">
					<h4>Assigned Training Programmes</h4>
					<small class="text-success">
						{!! count($completedTrainings) == 0 ? '' : '<i class="fas fa-shield-alt"></i><strong> ' . count($completedTrainings) . ' ' . ( count($completedTrainings) == 1 ? 'training' : 'trainings') . ' completed!</strong>' !!}
					</small>
				</div>
				<div class="col table-responsive">
					@if(count($userTrainings) != 0)
						<table class="table">
							<thead class="">
							<tr>
								<th scope="col" class="col-sm-1 align-middle">#</th>
								<th scope="col" class="col-sm-2 align-middle">Training Title</th>
								<th scope="col" class="col-sm-1 align-middle">Total Topic(s)</th>
								<th scope="col" class="col-sm-2 align-middle">Category</th>
								<th scope="col" class="col-sm-1 align-middle">Progress</th>
								<th scope="col" class="col-sm-2 align-middle">Assigned By</th>
								<th scope="col" class="col-sm-1 align-middle">Added on</th>
								<th scope="col" class="col-sm-2 align-middle"></th>
							</tr>
							</thead>
							<tbody>
							@foreach($userTrainings as $index => $userTraining)
								<tr>
									<td>{{ $index + 1 }}</td>
									<td><strong>{{ $userTraining->programme->name  ?? 'NA' }}</strong></td>
									<td class="d-flex justify-content-center">{{ $userTraining->programme->topics->count()  ?? 'NA' }}</td>
									<td>{{ $userTraining->category->name  ?? 'NA' }}</td>
									<td class="d-flex justify-content-start">{{ $userTraining->status ?: 'Pending' }}</td>
									<td>{{ App\Models\User::findOrFail($userTraining->programme->added_by)->name }}</td>
									<td>{{ $userTraining->created_at->format('d M, Y')  ?? 'NA' }}</td>
									<td class="d-flex justify-content-end">
										<a class="btn btn-outline-success btn-sm f-12 openRightModal" href="{{ route('config.startTraining', ['id' => $userTraining->programme->id]) }}"><i class="fa fa-play-circle mr-1"></i> Start Training</a>
									</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					@else
						<div class="alert alert-warning" role="alert">
							<i class="fa fa-info mx-2"></i> @lang('landingpagepro::app.message.noDataFound')
						</div>
					@endif
				</div>
			</div>

		</div>
	</div>
	<!-- INFOBOX WRAPPER END -->

@endsection

@push('scripts')
	<script>
		var el = document.getElementById('close-task-detail');
		if (el) {
			el.addEventListener("click", function () {
				var redirectUrlElement = document.getElementById('updateOnExit');
				if (redirectUrlElement) {
					var redirectUrl = redirectUrlElement.getAttribute('data-redirect-url');
					if (redirectUrl) {
						var xhr = new XMLHttpRequest();
						xhr.open('GET', redirectUrl);
						xhr.onload = function () {
							console.log(xhr.responseText);
						};
						xhr.send();
					}
				}
			});
		}
	</script>
@endpush
