@php
	//dd($tpProgramme);
@endphp
<style>
	.modal-fullscreen {
		width: 100%;
		height: 100%;
		margin: 0;
	}

	.modal-content {
		height: 100%;
		border-radius: 0;
	}

	.modal-dialog {
		margin: 0;
		width: 100%;
		height: 100%;
	}

	.modal-dialog.modal-xl {
		max-width: 100%;
	}

	.modal-body {
		overflow-y: auto;
	}

	.modal-footer {
		position: absolute;
		bottom: 0;
		width: 100%;
	}

</style>
<div
	id="notice-detail-section">
	<div class="row">
		<div class="col-sm-12">
			<div class="card bg-white border-0 b-shadow-4">
				<div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
					<div class="row">
						<div class="col-lg-10 col-10">
							<h4 class="heading-h4">@lang('trainingpro::app.header.trainingHeader')</h4>
							<span id="updateOnExit" data-redirect-url="{{ route('config.exitTraining', ['id' => $tpProgramme->id]) }}"></span>
						</div>
						<div class="col-lg-2 col-2 text-right"> <!--space for button/dropdown menu --></div>
					</div>
				</div>
				<div class="card-body">
					<div class="col table-responsive">
						<table class="table table-borderless">
							<thead>
							<tr>
								<th scope="col" class="col-sm-9">
									<div class="row align-items-center">
										<div class="col-auto pr-0">
											<img class="taskEmployeeImg rounded-circle" src="{{ $userDetails->image_url }}" alt="User Image">
										</div>
										<div class="col pl-2 d-flex align-items-center">
											<h3 class="heading-h3 m-0">{{ $userDetails->salutation->name ? ucfirst(strtolower($userDetails->salutation->name)) . '. ' : '' }}{{ ucwords(strtolower($userDetails->name)) }}</h3>
										</div>
									</div>
								</th>
								<th scope="col" class="col-sm-3 text-right">
									<h6 class="header-h6 m-0">Started At</h6>
									<strong>{{ $tpProgress->created_at->format('jS M, Y \a\t g:i A') }}</strong>
								</th>
							</tr>
							<tr>
								<th scope="col" class="col-sm-9">
									<p class="f-12 text-secondary">{{ $userDetails->employeeDetail->designation->name }}, {{ $userDetails->employeeDetail->department->team_name }}</p>
								</th>
								<th scope="col" class="col-sm-3 text-center">
									@if($tpProgramme->duration !== 0)
										@php
											$spentTimeInMinutes = ceil($tpProgress->spent_time / 60);
											$progressPercentage = ceil(($spentTimeInMinutes / $tpProgramme->duration) * 100);
										@endphp
										<div class="progress" style="height: 20px;">
											<div class="progress-bar {{ $progressPercentage < 50 ? 'bg-warning' : ($progressPercentage <= 100 ? 'bg-success' : 'bg-primary') }}" role="progressbar" style="width: {{ $progressPercentage }}%" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100">
												{{ $progressPercentage }} %
											</div>
										</div>
										<small><i class="fa fa-info-circle"></i> {{ $spentTimeInMinutes }} minutes spent of {{ $tpProgramme->duration }} minutes allocated!</small>
									@endif
								</th>
							</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>

		@if(count($tpTrainings) != 0)
			@foreach($tpTrainings as $index => $tpTraining)
				<div class="col-sm-12 mt-2">
					<div class="card bg-white border-0 b-shadow-4">
						<div class="card-body">
							<div class="col table-responsive">
								<table class="table">
									<tr>
										<td class="col-sm-8" rowspan="3">
											<div class="d-flex justify-content-center align-items-center">
												@if( $tpTraining->type === 'video' )
													<button type="button" class="btn btn-secondary" data-toggle="modal" data-type="video" data-target="#myModalXl" data-source="{{ $tpTraining->value }}">
														<i class="fa fa-play fa-6x"></i>
													</button>
												@else
													<button type="button" class="btn btn-secondary" data-toggle="modal" data-type="doc" data-target="#myModalXl" data-source="{{ $tpTraining->value }}">
														<i class="fa fa-book-open fa-6x"></i>
													</button>
												@endif
											</div>
										</td>
										<td class="col-sm-4">
											<strong><i class="fa fa-arrow-circle-right text-warning"></i> {{ $tpTraining->name }}</strong>
										</td>
									</tr>
									<tr>
										<td>
											<strong>Introduction:</strong>
											<p class="f-14">{{ $tpTraining->description }}</p>
										</td>
									</tr>
									<tr>
										<td>
											<strong>Type: {!! $tpTraining->type === 'video' ? '<i class="fa fa-film fa-1x"></i> Video' : ($tpTraining->type === 'pdf' ? '<i class="fa fa-file-pdf fa-1x"></i> PDF' : ($tpTraining->type === 'presentation' ? '<i class="fa fa-images fa-1x"></i> Presentation' : '')) !!}</strong>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			@endforeach

			@if( ceil($tpProgress->spent_time / 60) >= $tpProgramme->duration && !(empty($tpProgramme->assessment)) && $tpProgramme->assessment->is_enabled === 1)
				<div class="col-sm-12 mt-2">
					<div class="card bg-white border-0 b-shadow-4">
						<div class="card-body">
							<div class="row justify-content-end">
								<div class="col-3 d-flex justify-content-end">
									<a class="btn btn-outline-primary btn-sm mx-2 openRightModals" href="{{ route('config.start', ['id' => $tpProgramme->assessment->id]) }}" data-redirect-url="{{ route('config.trainings') }}">
										<i class="fa fa-edit"></i> Start Assessment
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			@else
				@if( empty($tpProgramme->assessment) || $tpProgramme->assessment->is_enabled === 0 )
					<div class="col-sm-12 mt-2">
						<div class="alert alert-info" role="alert"><i class="fa fa-info mx-2"></i> @lang('trainingpro::app.message.noAssesFound')</div>
					</div>
				@else
					<div class="col-sm-12 mt-2">
						<div class="alert alert-info" role="alert"><i class="fa fa-info mx-2"></i> @lang('trainingpro::app.message.notCompleted')</div>
					</div>
				@endif
			@endif
		@else
			<div class="alert alert-warning" role="alert"><i class="fa fa-info mx-2"></i> @lang('trainingpro::app.message.noDataFound')</div>
		@endif
	</div>
</div>

<div class="modal fade" id="youtubeModal" tabindex="-1" role="dialog" aria-labelledby="youtubeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-fullscreen" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="youtubeModalLabel"> YouTube Video</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<iframe width="100%" height="480" src="" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope; picture-in-picture"></iframe>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {

		var tag = document.createElement('script');
		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

		function createPlayer(videoId) {
			player = new YT.Player('player', {
				height: '100%',
				width: '100%',
				videoId: videoId,
				playerVars: {
					'playsinline': 1
				},
				events: {
					'onReady': onPlayerReady,
					'onStateChange': onPlayerStateChange
				}
			});
		}

		function onPlayerReady(event) {
			event.target.playVideo();
		}

		var done = false;

		function onPlayerStateChange(event) {
			if (event.data == YT.PlayerState.PLAYING && !done) {
				setTimeout(stopVideo, 6000);
				done = true;
			}
		}

		function stopVideo() {
			player.stopVideo();
		}

		$('#myModalXl').on('show.bs.modal', function (event) {
			// Create common elements for header and reset timer
			var timerSpan = $('<span>').attr('id', 'timer').addClass('mr-auto').text('00:00');
			var closeButton = $('<button>').attr({
				'type': 'button',
				'class': 'close',
				'data-dismiss': 'modal',
				'aria-label': 'Close'
			}).html('<span aria-hidden="true">&times;</span>');

			var modalHeader = $(this).find('.modal-header');
			modalHeader.empty().append(timerSpan, closeButton);

			// Reset timer
			resetTimer();

			// Get button data type and URL
			var button = $(event.relatedTarget);
			var dataType = button.data('type');
			var source = button.data('source');

			// Create content based on data type
			var modalBody = $(this).find('.modal-body');
			modalBody.empty();

			if (dataType === 'video') {
				var vPlayer = '<div id="player"></div>';
				modalBody.append(vPlayer);

				var videoId = new URL(source).searchParams.get('v');
				if (videoId) {
					createPlayer(videoId);
				} else {
					console.error("Invalid YouTube URL. Please provide a valid URL with video ID.");
				}
			} else if (dataType === 'doc') {
				var iframe = $('<iframe>').attr({
					'id': 'pdfViewer',
					'src': source,
					'frameborder': '0',
					'width': '100%',
					'height': '100%'
				});
				modalBody.append(iframe);
			} else {
				// Handle invalid data types if needed
				console.error("Invalid data type:", dataType);
			}
		});

		$('#myModalXl').on('hidden.bs.modal', function (e) {
			stopVideo();
			// Stop the timer when the modal is hidden
			clearInterval(timerInterval);
		});

		// Define variables for the timer
		var timerInterval;
		var startTime = 0;

		function resetTimer() {
			startTime = new Date().getTime(); // Reset start time
			clearInterval(timerInterval); // Clear any existing interval
			timerInterval = setInterval(updateTimer, 1000); // Start the timer interval
		}

		function updateTimer() {
			// Calculate elapsed time
			var currentTime = new Date().getTime();
			var elapsedTime = currentTime - startTime;

			// Convert elapsed time to seconds
			var elapsedSeconds = Math.floor(elapsedTime / 1000);

			// Calculate minutes and seconds
			var minutes = Math.floor(elapsedSeconds / 60);
			var seconds = elapsedSeconds % 60;

			// Format the elapsed time as MM:SS
			var formattedTime = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;

			// Update the timer display
			$('#timer').text(formattedTime);
		}
	});

	$('body').on('click', '.deleteQuestion', function () {
		Swal.fire({
			title: "@lang('messages.sweetAlertTitle')",
			text: "@lang('messages.recoverRecord')",
			icon: 'warning',
			showCancelButton: true,
			focusConfirm: false,
			confirmButtonText: "@lang('messages.confirmDelete')",
			cancelButtonText: "@lang('app.cancel')",
			customClass: {
				confirmButton: 'btn btn-primary mr-3',
				cancelButton: 'btn btn-secondary'
			},
			showClass: {
				popup: 'swal2-noanimation',
				backdrop: 'swal2-noanimation'
			},
			buttonsStyling: false
		}).then((result) => {
			if (result.isConfirmed) {
				var id = $(this).data('id');
				var url = '{{ route("config.destroyQa", ['id' => 'placeholder']) }}'.replace('placeholder', id);
				var token = "{{ csrf_token() }}";
				$.easyAjax({
					type: 'POST',
					url: url,
					data: {
						'_token': token,
						'_method': 'DELETE'
					},
					success: function (response) {
						if (response.status == "success") {
							window.location.href = response.redirectUrl;
						}
					}
				});
			}
		});
	});
</script>
