<!DOCTYPE html>

<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Font Awesome Icons -->
	<link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

	<!-- Template CSS -->
	<link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

	<!-- DatePicker CSS -->
	<link rel="stylesheet" href="{{ asset('vendor/css/datepicker.min.css') }}">

	<title>@lang($pageTitle)</title>
	<meta name="msapplication-TileColor" content="#ffffff">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ $company->favicon_url ?? '' }}">
	<meta name="msapplication-TileImage" content="{{ $company->favicon_url ?? '' }}">

	<meta name="theme-color" content="#ffffff">

	@include('sections.theme_css', ['company' => $company])

	@isset($activeSettingMenu)
		<style>
			.preloader-container {
				margin-left: 510px;
				width: calc(100% - 510px)
			}

		</style>
	@endisset

	@stack('styles')

	<style>
		:root {
			--fc-border-color: #E8EEF3;
			--fc-button-text-color: #99A5B5;
			--fc-button-border-color: #99A5B5;
			--fc-button-bg-color: #ffffff;
			--fc-button-active-bg-color: #171f29;
			--fc-today-bg-color: #f2f4f7;
		}

		.fc a[data-navlink] {
			color: #99a5b5;
		}

		img {
			width: 50px;
			margin-top: 20px;
		}

		.box {
			margin-top: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

	</style>

</head>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<body><!-- change dark theme class according to application dark theme setting -->
@php
	
@endphp
<div class="box">

	<div class="@if($styled == 1) col-md-6 @else col-md-12 @endif">

		<div class="card">
			<div class="card-body">
				<h5 class="card-title">Public Assessment</h5>
				<h4>{{$pap->assessment_name}}</h4>
				<p>{{ $product ? 'Assessment on: '.$product->name.'' : '' }}<br>
				{{$pap->description}}</p>
			</div>
		</div>
	
		<div class="card mt-3" id="cardAsseessResult" style="display:none">
		<div class="card-body">
			<h5 class="card-title">Your Assessment Result</h5>
			<h4>{{$pap->assessment_name}}</h4>
			<div id="resultData">
	
			</div>
		<div class="ml-auto"> <!-- Use ml-auto class to push buttons to the right -->
			<div class="d-flex justify-content-center">
				<button data-id="{ { $recentResult->id }}" type="button" class="btn btn-outline-warning download mx-1"><i class="fa fa-download"></i></button>
				<button data-id="{ { $recentResult->id }}" type="button" class="btn btn-outline-warning mail mx-1"><i class="fa fa-envelope"></i></button>
			</div>
		</div>
		</div>
		
		</div>


		<div class="white-box pt-20 border-dark mt-3">
			<x-form id="createPublicAsess">
				<div class="form-body">
					<div class="card">
						<div class="card-body">
							<div class="row">
								@php
					
								@endphp
								<div class="col-md-12">
									<input type="hidden" name="totalScore" value="{{ $totalScore }}">
									<input type="hidden" name="assessmentId" value="{{ $pap->id }}">
									<x-forms.text fieldId="participantName" fieldLabel="Full Name"
									fieldName="participantName" fieldRequired="true" fieldPlaceholder="Enter your full name.">
									</x-forms.text>
								</div>
								<div class="col-md-6">
									<x-forms.text fieldId="participantPhone" fieldLabel="Phone Number"
									fieldName="participantPhone" fieldRequired="true" fieldPlaceholder="Enter your phone number.">
									</x-forms.text>
								</div>
								<div class="col-md-6">
									<x-forms.text fieldId="participantEmail" fieldLabel="Email"
									fieldName="participantEmail" fieldRequired="true" fieldPlaceholder="Enter your email.">
									</x-forms.text>
								</div>
							</div>
						</div>
					</div>
					<div class="card mt-3">
						<div class="card-body">
							<div class="row">
							@if ($pap->assessment_type!=2)
							
								<div class="col mcq">
									@foreach ($groupedQuestions as $category => $questions)
										<p><strong>{{ $category }}</strong></p>

										@foreach ($questions as $index => $question)
											<div class="py-3 question">
												<p>{{ ($index+1).'. '. ucfirst($question->question) }}</p>

												<div class="form-check form-check-inline d-flex justify-content-around w-100">
													@foreach ($questionAns as $qanswer)
														@if ($question->id == $qanswer->question_id)
															<div class="custom-control custom-radio custom-control-inline">
																<input type="radio" class="form-check-input" id="answer_{{ $qanswer->anwser_id }}" name="answers[{{ $question->id }}]" value="{{ $qanswer->ans_code }}">
																<label class="form-check-label px-1" for="answer_{{ $qanswer->anwser_id }}">{{ ucfirst($qanswer->answer) }}</label>
															</div>
														@endif
													@endforeach
												</div>
											</div>
										@endforeach
										<hr>
									@endforeach
								</div>
							@else
								<div class="col ratted">
								@foreach ($groupedQuestions as $category => $questions)
								<p><strong>{{ $category }}</strong></p>

								@foreach ($questions as $question)
									<div class="py-3 question">
										<p>{{ $question->question }}</p>
										
										@php $rates = 10; @endphp
										<div class="form-check form-check-inline d-flex justify-content-around w-100"></div>
											@for ($index = 0; $index < $rates; $index++) 
												<div class="custom-control custom-radio custom-control-inline">
													<input type="radio" class="form-check-input" id="rating{{ $index + 1 }}" name="answers[{{ $question->id }}]" value="{{ $index + 1 }}">
													<label class="form-check-label px-1 mt-1" for="rating{{ $index + 1 }}">{{ $index + 1 }}</label>
												</div>
											@endfor
										</div>
								
								@endforeach
								<hr>
								@endforeach
								</div>
							@endif
							</div>
						</div>
					</div>		
				</div>
					
				<br><br>
				<div class="form-actions">
							<input type="hidden" name="company_id" value="{{ $pap->company_id }}">
							<button type="button" id="save-form" class="btn btn-primary"><i
									class="fa fa-check"></i> @lang('app.save')</button>
							<button type="reset" class="btn btn-secondary ml-3">@lang('app.reset')</button>
				</div>
			</x-form>
			<br><br>
			<!-- Temorarly showing validations here as UI validation conflicts. check later -->
			<div class="row">
				<div class="col-sm-12 col-md-12">
					<div id="validation-errors" style="display:none; color:red;"></div>
					<div class="alert alert-success" id="success-message" style="display:none"></div>
				</div>
			</div
		</div>
		
	</div>
</div>

</body>


<!-- jQuery -->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

<!-- Global Required Javascript -->
<script src="{{ asset('vendor/bootstrap/javascript/bootstrap-native.js') }}"></script>

<!-- Font Awesome -->
<script src="{{ asset('vendor/jquery/all.min.js') }}"></script>

<!-- Template JS -->
<script src="{{ asset('js/main.js') }}"></script>

<script>
	const MODAL_LG = '#myModal';
	const MODAL_XL = '#myModalXl';
	document.loading = '@lang('app.loading')';
	const dropifyMessages = {
		default: "@lang('app.dragDrop')",
		replace: "@lang('app.dragDropReplace')",
		remove: "@lang('app.remove')",
		error: "@lang('messages.errorOccured')",
	};

	$(window).on('load', function () {
		// Animate loader off screen
		init();
		$(".preloader-container").fadeOut("slow", function () {
			$(this).removeClass("d-flex");
		});
	});

</script>
<script>
	$('#save-form').click(function () {
		$('#validation-errors').html('');
		$.ajax({
			url: "{{route('front.store-public-assessement')}}",
			container: '#createPublicAsess',
			type: "POST",
			redirect: true,
			disableButton: true,
			file: true,
			blockUI: true,
			data: $('#createPublicAsess').serialize(),
			success: function (response) {
				if (response.status == "success") {
					$('#createPublicAsess')[0].reset();
					$('#createPublicAsess').hide();
					$('#success-message').html(response.message);
					$('#success-message').show();
					
					//$('#resultData').html(response.data);
					//$('#cardAsseessResult').show();
					
				}
			},
			error: function(error) {
				if (error.status === 422) {
					let errorMessage = '';
					// Handle validation errors under 'message'
					if (error.responseJSON.message) {
						$.each(error.responseJSON.message, function (key, value) {
							errorMessage += value + '<br>'; // Use <br> for line breaks in HTML
						});
					}
					$('#validation-errors').html(errorMessage).show(); // Display errors
				} else if (error.status === 403) {
					alert(error.responseJSON.message);  // ToDO - change later
				} else {
					console.error("An error occurred:", error);
					alert("An unexpected error occurred. Please try again.");
				}
        	}		
		});
	});
</script>
</html>
