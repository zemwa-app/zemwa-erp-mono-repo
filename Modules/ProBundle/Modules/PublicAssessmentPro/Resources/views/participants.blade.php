@extends('layouts.app')

@push('datatable-styles')

@endpush

@php
$companyId = company()->id;

if (!function_exists('getIconClass')) {
	function getIconClass($minScore, $maxScore, $score)
	{
		if ($score < $minScore) {
			return 'fa fa-exclamation-circle text-danger'; // Replace 'fa-victory' with the class name for victory icon
		} elseif ($score == $maxScore) {
			return 'fa-graduation-cap text-warning'; // Replace 'fa-awesome' with the class name for awesome icon
		} else {
			return 'fa-thumbs-up text-success'; // Replace 'fa-failed' with the class name for failed icon
		}
	}
}
if (!function_exists('getStatus')) {
	function getStatus($minScore, $maxScore, $score) {
		if ($score < $minScore) {
			return '<span class="f-18 font-weight-bold text-danger">Not Satisfactory!</span>';
		} elseif ($score == $maxScore) {
			return '<span class="f-18 font-weight-bold text-blue">Excellent!</span>';
		} else {
			return '<span class="f-18 font-weight-bold text-success">Cleared!</span>';
		}
	}
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
	<!-- MODULE MENU END -->

	<!-- CONTENT WRAPPER START -->
	<div class="container-fluid">
		<div class="row p-3">
			<div class="col-sm-12 rounded border border-grey bg-white py-4 px-1">
				<div class="col-sm-12 mb-2 pb-2 border-bottom-grey"><h4>Public Assessment Summary</h4></div>
				<div class="col table-responsive">
					<div class="row d-flex justify-content-around">
						@if( $recentResults !== null && !$recentResults->isEmpty() )
							@foreach($recentResults as $index => $recentResult)
							
								<div class="col-sm-12 col-md-6 m-0 px-1">
									<div class="card p-0 box-shadow-hover mb-2" data-toggle="collapse" data-target="#collapseBody{{ $index }}" style="cursor:pointer;">
										<div class="card-header bg-dark text-light">
											<div class="row m-0">
												<div class="col-sm-12 m-0 mb-1 d-flex align-items-center"><h6 class="m-0"><i class="fa fa-tasks"></i> {{ $recentResult->assessment->assessment_name }}</h6></div>
												<div class="col-sm-12 m-0 d-flex">
													<!--span class="f-6" style="font-size: 11px;color:#908f8f;">[Click for details]</span-->
													@if($recentResult->assessment->assessment_type==0)
													<div class="ml-auto d-flex justify-content-end align-items-center"><i class="fa {{ getIconClass($recentResult->assessment->min_score, $recentResult->assessment->max_score, $recentResult->scored_mark) }} mr-2"></i>
														Scored:
														<strong class="ml-1 my-0 f-14">{{$recentResult->grade}}%</strong>
													</div>
													@endif
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
																	{{ $recentResult->participant_name }}
																	<small class='d-block m-0 f-12'>{{ $recentResult->participant_email }}</small>
																</div>
															</div>
														</h1>
														<p class="f-11 mt-0 mb-2 border-bottom">
			
															{{ $recentResult->assessment->assessment_name }}
														</p>
														
														@if($recentResult->assessment->assessment_type==0)
														<p class="lead f-14 m-0 mb-1 px-1">
															Max. Score: {{ $recentResult->assessment->max_score }} &#8214; Min. Score: {{ $recentResult->assessment->min_score }}
														</p>
														<p class="lead f-14 m-0 mb-1 px-1">
															<span class="font-weight-bold">Score:</span> {{ $recentResult->scored_mark }} marks
														</p>
														<p class="lead f-14 m-0 mt-4 mb-1 px-1">
															<span class="d-block border-bottom">Assessment Status:</span>
															<strong>{!! getStatus($recentResult->assessment->min_score, $recentResult->assessment->max_score, $recentResult->scored_mark) !!}</strong>
														</p>
														@endif
													</div>
													<div class="col-sm-5 position-relative image-bg" style="background-image: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), url({{ Module::asset('publicassessmentpro:images/trophytrans.png') }});">
														<div class="border-bottom mb-2 pb-1">
															<div class="text-right f-11">Assessment Date</div>
															<div class="text-right f-13 mb-0">{{ \Carbon\Carbon::parse($recentResult->submitted_on)->format('jS M, Y \a\t H:i:s') }}</div>
														</div>
														@if ($recentResult->assessment->product_id)
														@php //$product = Product::select('name')->where('id', $recentResult->assessment->product_id)->first();
														  $product = DB::select("SELECT p.name FROM products p WHERE p.id = ? AND p.company_id = ?", [$recentResult->assessment->product_id,$companyId]);
														@endphp
														<p class="f-14 font-weight-bold text-right mb-0">
														Product: {{ strtoupper($product[0]->name) }} </p>
														@else
														<p class="f-14 font-weight-bold text-right mb-0">
														Product/Service: Other  </p>
														@endif
													</div>
												</div>
											</div>
											<div class="card-footer d-flex align-items-center justify-content-center bg-light border-top-1 border-warning pl-0">
												<div class="text-center">
													<img src="{{ Module::asset('publicassessmentpro:images/zemwa_newogo-transperent.png') }}" style="width: 100px;" alt="">
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
									<i class="fa fa-info mx-2"></i> @lang('publicassessmentpro::app.message.noDataFound') No data
								</div>
							</div>
						@endif
					</div>
					<!-- <div class="row d-flex justify-content-around mt-3">
					add pagination later	
					</div> -->
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
