@extends('layouts.app')

@push('datatable-styles')

@endpush

@php
	$companyId = company()->id;
//		dd(company());
	// dd();
	function getIconClassR($minScore, $maxScore, $score)
	{
		if ($score < $minScore) {
			return 'fa fa-exclamation-circle text-danger'; // Replace 'fa-victory' with the class name for victory icon
		} elseif ($score == $maxScore) {
			return 'fa-graduation-cap text-warning'; // Replace 'fa-awesome' with the class name for awesome icon
		} else {
			return 'fa-thumbs-up text-success'; // Replace 'fa-failed' with the class name for failed icon
		}
	}
	function getStatusR($minScore, $maxScore, $score) {
		if ($score < $minScore) {
			return '<span class="f-18 font-weight-bold text-danger">Not Satisfactory!</span>';
		} elseif ($score == $maxScore) {
			return '<span class="f-18 font-weight-bold text-blue">Excellent Performance!</span>';
		} else {
			return '<span class="f-18 font-weight-bold text-success">Cleared!</span>';
		}
	}
	function getPercentageR($marks, $maxMarks) {
		if ($maxMarks === 0) {
			return 0;
		}
		return number_format(($marks / $maxMarks) * 100, 0);
	}
@endphp

@push('styles')
	<style>
		.box-shadow-hover:hover {
			box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
		}

		.image-bg {
			background-repeat: no-repeat;
			background-position: bottom right;
			background-size: 80%; /* Adjust this value to resize your background image */
		}
	</style>
@endpush

@section('content')
	<!-- MODULE MENU START -->
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
	<!-- MODULE MENU END -->

	<!-- CONTENT WRAPPER START -->
	<div class="container-fluid">
		<div class="row p-3">
			<div class="col-sm-12 rounded border border-grey bg-white py-4 px-1">
				<div class="col-sm-12 mb-2 pb-2 border-bottom-grey"><h4>Assessment Summary</h4></div>
				<div class="col table-responsive">
					<div class="row d-flex justify-content-around">
						@if( $recentResults !== null && !$recentResults->isEmpty() )
							@foreach($recentResults as $index => $recentResult)
								<div class="col-sm-12 col-md-6 m-0 px-1">
									<div class="card p-0 box-shadow-hover mb-2" data-toggle="collapse" data-target="#collapseBody{{ $index }}" style="cursor:pointer;">
										<div class="card-header bg-dark text-light">
											<div class="row m-0">
												<div class="col-sm-12 m-0 mb-1 d-flex align-items-center"><h6 class="m-0"><i class="fa fa-shield-alt"></i> {{ $recentResult->assessment->name }}</h6></div>
												<div class="col-sm-12 m-0 d-flex">
													<!--span class="f-6" style="font-size: 11px;color:#908f8f;">[Click for details]</span-->
													<div class="ml-auto d-flex justify-content-end align-items-center"><i class="fa {{ getIconClassR($recentResult->min_score, $recentResult->max_score, $recentResult->score) }} mr-2"></i>
														Overall Score:
														<strong class="ml-1 my-0 f-14">{{ getPercentageR($recentResult->score, $recentResult->max_score) }}%</strong>
													</div>
												</div>
											</div>
										</div>
										<div id="collapseBody{{ $index }}" class="card-content collapse">
											<div class="card-body pb-1">
												<div class="row">
													<div class="col-sm-7">
														<h1 class="heading-h1 border-bottom pb-2 mb-1">
															<div class="d-flex content-justify-center align-items-center">
																<div class='d-inline-block pr-3 mr-3 border-right border-warning'><img class='taskEmployeeImg rounded-circle' src='{{ $user->image_url }}'></div>
																<div class='d-inline-block mr-1'>
																	{{ $recentResult->user->name }}
																	<small class='d-block m-0 f-12'>{{ $recentResult->user->email }}</small>
																</div>
															</div>
														</h1>
														<p class="f-11 mt-0 mb-2 border-bottom">
															{{ $recentResult->user->employeeDetail->designation->name }}/
															{{ $recentResult->user->employeeDetail->department->team_name }}
														</p>
														<p class="lead f-14 m-0 mb-1 px-1">
															Max. Score: {{ $recentResult->assessment->max_score }} &#8214; Min. Score: {{ $recentResult->assessment->min_score }}
														</p>
														<p class="lead f-14 m-0 mb-1 px-1">
															<span class="font-weight-bold">Score:</span> {{ $recentResult->score }} marks
														</p>
														<p class="lead f-14 m-0 mt-4 mb-1 px-1">
															<span class="d-block border-bottom">Assessment Status:</span>
															<strong>{!! getStatusR($recentResult->min_score, $recentResult->max_score, $recentResult->score) !!}</strong>
														</p>
													</div>
													<div class="col-sm-5 position-relative image-bg" style="background-image: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), url({{ Module::asset('trainingpro:images/trophytrans.png') }});">
														<div class="border-bottom mb-2 pb-1">
															<div class="text-right f-11">Assessment Date</div>
															<div class="text-right f-13 mb-0">{{ \Carbon\Carbon::parse($recentResult->created_at)->format('jS M, Y \a\t H:i:s') }}</div>
														</div>
														<p class="f-14 font-weight-bold text-right mb-0">Employee ID: {{ strtoupper($recentResult->user->employeeDetail->employee_id) }}</p>
													</div>
												</div>
											</div>
											<div class="card-footer d-flex align-items-center justify-content-center bg-light border-top-1 border-warning pl-0">
												<div class="text-center">
													<img src="{{ Module::asset('trainingpro:images/zemwa_newogo-transperent.png') }}" style="width: 100px;" alt="">
												</div>
												<div class="ml-auto"> <!-- Use ml-auto class to push buttons to the right -->
													<div class="d-flex justify-content-center">
														<button data-id="{{ $recentResult->id }}" type="button" class="btn btn-outline-warning download mx-1"><i class="fa fa-download"></i></button>
														<button data-id="{{ $recentResult->id }}" type="button" class="btn btn-outline-warning mail mx-1"><i class="fa fa-envelope"></i></button>
													</div>
												</div>
											</div>


										</div>
									</div>
								</div>
							@endforeach
						@else
							<div class="col-sm-12">
								<div class="alert alert-warning" role="alert">
									<i class="fa fa-info mx-2"></i> @lang('trainingpro::app.message.noDataFound') No data
								</div>
							</div>
						@endif
					</div>
					<!--div class="row d-flex justify-content-around mt-3">
						{ { $recentResults->links('vendor.pagination.bootstrap-4') } }
					</div-->
				</div>
			</div>

		</div>
	</div>
	<!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
	<script>
		$(document).ready(function () {
			$('.card').click(function () {
				var target = $(this).data('target');
				$(target).collapse('toggle');
				$(target).collapse({transitionDuration: 500});
				$('.card-content.show').not(target).collapse('hide');
			});
		});
	</script>
@endpush
