@extends('layouts.app')

@push('datatable-styles')

@endpush

@php

	$companyId = company()->id;


@endphp

@section('content')
	<div class="container-fluid">
		<div class="row p-3 justify-content-around">
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
	</div>

	<!-- INFOBOX WRAPPER START -->
	<div class="container-fluid">
		<div class="row p-3 justify-content-around">
			<div class="col-sm-6 col-md-3 rounded border border-grey bg-white py-4">
				<div class="text-center text-danger">
					<div class="rounded-circle mb-4">
						<span class="fas fa-tasks fa-3x"></span>
					</div>
					<h2 class="h5 mb-4">{{ $assessments }} Assessment</h2>
				</div>
			</div>
		

			<div class="col-sm-6 col-md-3 rounded border border-grey bg-white py-4">
				<div class="text-center text-danger">
					<div class="rounded-circle mb-4">
						<span class="fas fa-user fa-3x"></span>
					</div>
					<h2 class="h5 mb-4">{{ $participants }} Paricipants</h2>
				</div>
			</div>
		</div>
	</div>
	<!-- INFOBOX WRAPPER END -->

	<!-- INFOBOX WRAPPER START -->
	<div class="container-fluid">
		<div class="row p-3">
			<div class="col-sm-12 col-md-6 rounded border border-grey bg-white py-4">
				<div class="col-sm-12 mb-2 pb-2 border-bottom-grey"><h4>Latest Assessments:</h4><small>(Created this month)</small></div>
				<div class="col table-responsive">
					@if(count($recentAssessments) != 0)
						<table class="table">
							<thead class="">
							<tr>
								<th scope="col" class="col-sm-1 align-middle">#</th>
								<th scope="col" class="col-sm-2 align-middle">Assessment Title</th>
								<th scope="col" class="col-sm-1 align-middle">Product/Others</th>
								<th scope="col" class="col-sm-1 align-middle">Total Views</th>
								<th scope="col" class="col-sm-2 align-middle">Created By</th>
								<th scope="col" class="col-sm-2 align-middle">Added on</th>
							</tr>
							</thead>
							<tbody>
							@foreach($recentAssessments as $index => $recentAssessment)
								<tr>
									<td>{{ $index + 1 }}</td>
									<td>{{ $recentAssessment->assessment_name ?: 'Not defined' }}</td>
									<td>{{ $recentAssessment->product ?: 'Not defined' }}</td>
									<td>{{ $recentAssessment->view_count ?: 'Not defined' }}</td>
									<td>{{ $recentAssessment->username ?: 'Not defined' }}</td>
									<td>{{ $recentAssessment->created_at->format('d M, Y') ?: 'Not defined' }}</td>
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

			<div class="col-sm-12 col-md-6 rounded border border-grey bg-white py-4">
				<div class="col-sm-12 mb-2 pb-2 border-bottom-grey"><h4>Assessment Participants:</h4><small>(Submitted this month)</small></div>
				<div class="col table-responsive">
					@if(count($recentResults) != 0)
						<table class="table">
							<thead>
							<tr>
								<th scope="col" class="col-sm-1 align-middle">#</th>
								<th scope="col" class="col-sm-2 align-middle">Name</th>
								<th scope="col" class="col-sm-3 align-middle">Assessment</th>
								<th scope="col" class="col-sm-1 align-middle">Score</th>
								<th scope="col" class="col-sm-2 align-middle">Submitted On</th>
							</tr>
							</thead>
							<tbody>
							@foreach($recentResults as $index => $recentResult)
								<tr>
									<td>{{ $index + 1 }}</td>
									<td>{{ $recentResult->participant_name ?: 'Not defined' }}</td>
									<td>{{ $recentResult->assessment ?: 'Not defined' }}</td>
									<td>{{ $recentResult->scored_mark ?: 'Not defined' }}</td>
									<td>{{ $recentResult->updated_at ? \Carbon\Carbon::parse($recentResult->updated_at)->format('d M, Y') : 'Not defined' }}
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
		$(document).ready(function () {

		});
	</script>
@endpush
