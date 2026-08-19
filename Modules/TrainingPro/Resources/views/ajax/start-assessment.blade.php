@extends('layouts.app')

@push('datatable-styles')
@endpush

@php
	$companyId = company()->id;
	$totalTime = $tpAssessment->duration * 60;
@endphp

@push('styles')
	<style>
		.avatar-container {
			position: relative;
			display: inline-block;
		}

		.user-avatar {
			border-radius: 50%; /* Adjust as needed */
			width: 100px; /* Adjust as needed */
			height: 100px; /* Adjust as needed */
		}

		.loader {
			position: absolute;
			top: 0;
			left: 0;
			border: 3px solid #1da001;
			border-radius: 50%;
			width: 100%;
			height: 100%;
		}

		.loader--hidden {
			opacity: 0; /* Hide the loader initially */
		}

		.loader.animate {
			animation: fill linear forwards {{ $totalTime }}s; /* Adjust animation duration */
		}

		@keyframes fill {
			0% {
				clip-path: polygon(50% -20.71%, 50% 50%, 50% 0%, 50% 0%, 50% 0%, 50% 0%, 50% 0%);
			}
			12.5% {
				clip-path: polygon(50% -20.71%, 50% 50%, 100% 0%, 100% 0%, 100% 0%, 100% 0%, 100% 0%);
			}
			25% {
				clip-path: polygon(50% -20.71%, 50% 50%, 120.71% 50%, 120.71% 50%, 120.71% 50%, 120.71% 50%, 100% 0%);
			}
			37.5% {
				clip-path: polygon(50% -20.71%, 50% 50%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 0%);
			}
			50% {
				clip-path: polygon(50% -20.71%, 50% 50%, 50% 120.71%, 50% 120.71%, 50% 120.71%, 100% 100%, 100% 0%);
			}
			62.5% {
				clip-path: polygon(50% -20.71%, 50% 50%, 0% 100%, 0% 100%, 0% 100%, 100% 100%, 100% 0%);
			}
			75% {
				clip-path: polygon(50% -20.71%, 50% 50%, -20.71% 50%, -20.71% 50%, 0% 100%, 100% 100%, 100% 0%);
			}
			87.5% {
				clip-path: polygon(50% -20.71%, 50% 50%, 0% 0%, 0% 0%, 0% 100%, 100% 100%, 100% 0%);
			}
			100% {
				clip-path: polygon(50% -20.71%, 50% 50%, 50% -20.71%, 0% 0%, 0% 100%, 100% 100%, 100% 0%);
			}
		}
	</style>
@endpush

@section('content')
	<div class="container-fluid">
		<div class="row p-1 justify-content-around">
			<div class="col-sm-12 rounded border border-grey bg-white py-4 text-right">
				@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()))
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('trainingpro.index') }}"><i class="fa fa-home"></i></a>
					<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.home') }}"><i class="fa fa-cog"></i> Configuration</a>
				@endif
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.trainings') }}"><i class="fa fa-tasks"></i> My Trainings</a>
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.assessments') }}"><i class="fa fa-poll"></i> My Assessments</a>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row p-1">
			<div class="hidden-sm col-md-1 bg-white py2 d-flex justify-content-center align-items-center">
				<svg xmlns="http://www.w3.org/2000/svg" id="mdi-shield-edit-outline" viewBox="0 0 24 24">
					<path d="M21.7 13.6L20.4 12.3C20.3 12.2 20.2 12.1 20 12.1C19.9 12.1 19.7 12.2 19.6 12.3L18.6 13.3L20.6 15.3L21.6 14.3C21.9 14.1 21.9 13.8 21.7 13.6M12 19.9V22H14.1L20.2 15.9L18.2 13.8L12 19.9M10 22.3C5.9 20.3 3 15.8 3 11V5L12 1L21 5V8.1L19 10.1V6.3L12 3.2L5 6.3V11.2C5 14.7 7.2 18.3 10 20.1V22.3Z"/>
				</svg>
			</div>
			<div class="col-sm-12 col-md-8 bg-white py-2 d-flex align-items-center">
				<div>
					<h3 class="heading-h3 m-0">{{ $tpAssessment->name }}</h3>
					<p class="text-secondary m-0">{{ $tpAssessment->description }}</p>
				</div>
			</div>
			<div class="col-sm-12 col-md-3 bg-white py-2 d-flex justify-content-end align-items-center">
				<button id="start-button" data-id="{{ $tpAssessment->id }}" class="btn btn-outline-success btn-sm mx-1"><i class="fa fa-play"></i> Start</button>
				<button id="finish-button" class="btn btn-outline-secondary btn-sm mx-1" data-id="{{ $tpAssessment->id }}" disabled="true"><i class="fa fa-stop"></i> Finish</button>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row p-1">
			<div class="col-sm-12 col-md-2 bg-white py-2 d-flex justify-content-center align-items-center">
				<div class="avatar-container">
					<img class="user-avatar rounded-circle" src="{{ $userDetails->image_url }}" alt="User Image">
					<div class="loader loader--hidden"></div>
				</div>
			</div>
			<div class="col-sm-12 col-md-6 bg-white py-2 d-flex align-items-center">
				<div class="">
					<h3 class="heading-h3 m-0">{{ $userDetails->salutation->name ? ucfirst(strtolower($userDetails->salutation->name)) . '. ' : '' }}{{ ucwords(strtolower($userDetails->name)) }}</h3>
					<p class="f-12 m-0 text-secondary">{{ $userDetails->employeeDetail->designation->name }}, {{ $userDetails->employeeDetail->department->team_name }}</p>
				</div>
			</div>
			<div class="col-sm-12 col-md-4 bg-white py-2 d-flex align-items-center">
				<div>
					<h6 class="heading-h6">Duration: {{ $tpAssessment->duration }} mins. | Max. Score: {{ $tpAssessment->max_score }} | Min. Score: {{ $tpAssessment->min_score }}</h6>
					<h6 class="heading-h6">Started at: 22 Jan, 2024 at 12:40 Am</h6>
					<h4 id="rTimer" class="heading-h3 text-secondary">Remaining TIme: <span id="remaining-time">{{ gmdate('H:i:s', $tpAssessment->duration * 60) }}</span></h4>
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row p-1 bg-amt-grey">
			<div class="hidden-sm col-md-2 d-flex justify-content-center align-items-center">
				<i class="fa fa-info-circle text-white" aria-hidden="true" style="font-size: 50%; height:50%; width:auto;"></i>
			</div>
			<div class="col-sm-12 col-md-10 d-flex justify-content-start align-items-center text-blue py-2 rounded-lg" style="background-color: rgba(23, 162, 184, .2);">
				<div>
					<div class="row">
						<div class="col">
							<h3 class="heading-h3">INSTRUCTIONS:</h3>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<ul class="d-block list-unstyled">
								<li><i class="fa fa-arrow-right" aria-hidden="true"></i> Please read all questions carefully before answering.</li>
								<li><i class="fa fa-arrow-right" aria-hidden="true"></i> For assessments with a set duration (excluding "No Limit"), you will be granted <strong>only one attempt</strong> to complete it.</li>
								<li><i class="fa fa-arrow-right" aria-hidden="true"></i> Click <strong>"Finish"</strong> only when you are finished and ready to submit your answers.</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="preloader-container justify-content-center align-items-center" style="display: none;">
		<div class="spinner-border" role="status" aria-hidden="true"></div>
	</div>

	<div id="question-container"></div>
@endsection

@push('scripts')
	<script>
		$(document).ready(function () {
			// const avatarBorder = $('.avatar-border');
			const loader = $('.loader');
			const questionContainer = $('#question-container');
			const remainingTimeContainer = $('#remaining-time');
			const finishButton = $('#finish-button');
			let timerStarted = false;
			let totalTime;

			$('#start-button').click(function () {
				Swal.fire({
					title: 'Are you sure?',
					text: 'Confirmation grants a single attempt for completion. Please proceed with caution.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Confirm',
					cancelButtonText: 'Cancel',
					confirmButtonColor: '#d33',
					cancelButtonColor: '#3085d6',
					reverseButtons: true,
				}).then((result) => {
					if (result.value) {  // Check if the confirm button was clicked
						$('.preloader-container').addClass('d-flex').show();
						if (!timerStarted) {
							var id = $(this).data('id');
							var url = '{{ route("config.get-qa", ['id' => 'placeholder']) }}'.replace('placeholder', id);
							// Fetch questions and answers via AJAX
							$.ajax({
								url: url,
								method: 'GET',
								success: function (response) {
									if (response.status === "success") {
										$('.preloader-container').removeClass('d-flex').hide();
										$('#rTimer').prop('disabled', true).addClass('text-danger').removeClass('text-secondary');
										$('#start-button').prop('disabled', true).addClass('btn-outline-secondary').removeClass('btn-outline-success');
										finishButton.prop('disabled', false).addClass('btn-outline-danger');
										questionContainer.html("");
										questionContainer.html(response.data);
										totalTime = parseInt({{ $tpAssessment->duration }}) * 60;
										startTimer(totalTime);
									} else {
										$('.preloader-container').removeClass('d-flex').hide();
										remainingTimeContainer.html("00:00:00");
										$('#start-button').prop('disabled', true).addClass('btn-outline-secondary').removeClass('btn-outline-success');
										finishButton.prop('disabled', true).addClass('btn-outline-secondary').removeClass('btn-outline-danger');
										$('#assessmentForm :input').prop('disabled', true);
										clearInterval(intervalId);
										clearInterval();
										Swal.fire('Error', response.message, 'error');
									}
								},
								error: function (error) {
									clearInterval(intervalId);
									clearInterval();
								}
							});
							timerStarted = true;
							startSendingFormData();
						}
					} else if (result.dismiss === Swal.DismissReason.cancel) {
						// Operation cancelled by user
						Swal.fire('Cancelled', 'Your assessment confirmation has been withdrawn.  If you\'d like to reschedule the assessment for a more suitable time, you can do so by visiting the \'My Training\' section within your account.', 'info');
					}
				});
			});

			function startTimer(totalTime) {
				let startTime = Date.now();
				let elapsedTime = 0;

				function updateTimer() {
					elapsedTime = Math.floor((Date.now() - startTime) / 1000);
					let progress = elapsedTime / totalTime * 100;
					const remainingTime = Math.max(0, totalTime - elapsedTime); // Ensure remaining time doesn't go negative
					// Update remaining time display in min:sec format
					const hours = Math.floor(remainingTime / 3600).toString().padStart(2, '0');
					const minutes = Math.floor((remainingTime % 3600) / 60).toString().padStart(2, '0');
					const seconds = Math.floor(remainingTime % 60).toString().padStart(2, '0');
					const formattedTime = `${hours}:${minutes}:${seconds}`;

					remainingTimeContainer.text(formattedTime);

					if (elapsedTime >= totalTime) {
						sendFormData(true);
						remainingTimeContainer.html("00:00:00");
						finishButton.prop('disabled', true).addClass('btn-outline-secondary').removeClass('btn-outline-danger');
						$('#assessmentForm :input').prop('disabled', true);
						clearInterval(intervalId);
						Swal.fire({
							icon: 'success',
							title: 'Completed!',
							text: 'Your assessment time is completed. You can view your score and other assessment related information on "My Assessment" section within your account.',
							confirmButtonText: 'OK'
						}).then((result) => {
							clearInterval();
							window.location.href = "{{ route('config.finish') }}";
						});
					} else {
						// Update animation properties dynamically
						if (!elapsedTime) {
							loader.removeClass('loader--hidden');
							loader.addClass('animate');
						}
						requestAnimationFrame(updateTimer);
					}
				}

				// Use requestAnimationFrame for smoother animation
				requestAnimationFrame(updateTimer);
			}

			finishButton.click(function () {
				Swal.fire({
					title: 'Are you sure?',
					text: 'Are you sure you want to submit your assessment? Please note that, you will be granted only one attempt to complete it.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Confirm',
					cancelButtonText: 'Cancel',
					confirmButtonColor: '#d33',
					cancelButtonColor: '#3085d6',
					reverseButtons: true,
				}).then((result) => {
					if (result.isConfirmed) {
						sendFormData(true).then((assessmentResult) => {
							if (assessmentResult === true) {
								Swal.fire({
									icon: 'success',
									title: 'Successful!',
									text: 'Your assessment has been submitted successfully. Click "View Assessment" button to view your score.',
									confirmButtonText: 'OK'
								}).then((result) => {
									finishButton.prop('disabled', true).addClass('btn-outline-secondary').removeClass('btn-outline-danger');
									$('#assessmentForm :input').prop('disabled', true);
									remainingTimeContainer.html("00:00:00");
									clearInterval();
									clearInterval(intervalId);
									console.log('result', assessmentResult);
									window.location.href = "{{ route('config.finish') }}";
								});
							}
						});
					} else if (result.dismiss === Swal.DismissReason.cancel) {
						// Operation cancelled by user
						Swal.fire('Continue Your Assessment', 'Please note that, you will be granted only one attempt to complete it.', 'info');
					}
				});
			});
		});

		let intervalId;

		function sendFormData(aStatus = false) {
			const form = document.getElementById("assessmentForm");
			if (!form) {
				return Promise.reject('Form element not found');
			}

			const formData = new FormData(form);
			const csrfToken = $('meta[name="csrf-token"]').attr('content');
			formData.append('_token', csrfToken);
			formData.append('status', aStatus);

			// Get all question radio groups (assuming unique question names)
			const processedQuestions = new Set(); // Track processed questions to avoid duplicates

			$('input[type="radio"]', form).each(function () {
				const questionName = $(this).attr('name');

				// Only process each question once
				if (processedQuestions.has(questionName)) {
					return;
				}
				processedQuestions.add(questionName);

				// Check if any radio button is checked for this question
				const selectedRadioButton = $('input[name="' + questionName + '"]:checked', form);
				if (selectedRadioButton.length > 0) {
					// Append the selected value to the formData
					formData.append(questionName, selectedRadioButton.val());
				} else {
					// If no radio button is checked, append 'na' as the default value
					formData.append(questionName, 'na');
				}
			});

			const url = '{{ route("config.assessment-stamp") }}';

			return fetch(url, {
				method: "POST",
				body: formData,
			})
				.then((response) => {
					console.log('formSubmitted', response);
					if (response.ok) {
						return true;
					} else {
						return false;
					}
				})
				.catch((error) => {
					return false;
				});
		}

		function sendFormDataIfFormExists() {
			const form = document.getElementById("assessmentForm");

			if (form) {
				// Form exists, send the data
				sendFormData();
			} else {
				// Form doesn't exist yet, wait for the next interval
				console.log("Form not found yet, waiting for next interval...");
			}
		}

		function startSendingFormData() {
			clearInterval(intervalId); // Clear any existing interval
			intervalId = setInterval(sendFormDataIfFormExists, 5000); // Start the interval
		}

		window.addEventListener('beforeunload', function (event) {
			// Trigger your event or perform any actions here
			userLeft();
		});
		document.addEventListener('visibilitychange', function () {
			if (document.visibilityState === 'hidden') {
				// User navigated away or minimized the browser
				userLeft();
			}
		});

		function userLeft() {
			// Your custom function to handle when the user navigates away
			sendFormData();
		}
	</script>
@endpush
