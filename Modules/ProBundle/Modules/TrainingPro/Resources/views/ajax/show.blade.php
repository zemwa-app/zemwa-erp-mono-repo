@php

@endphp
<div id="notice-detail-section">
	<div class="row">
		<div class="col-sm-12">
			<div class="card bg-white border-0 b-shadow-4">
				<div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
					<div class="row">
						<div class="col-lg-10 col-10">
							<h4 class="heading-h4">@lang('trainingpro::app.header.qaListingHeader') "{{ $tpAssessment->name }}"</h4>
						</div>
						<div class="col-lg-2 col-2 text-right"><!--space for button/dropdown menu --></div>
					</div>
				</div>
				<div class="card-body">
					<div class="col table-responsive">
						@if(count($tpQuestions) != 0)
							<table class="table">
								<thead class="">
								<tr>
									<th scope="col" class="col-sm-1">#</th>
									<th scope="col" class="col-sm-4">Assessment Question</th>
									<th scope="col" class="col-sm-4">Options (with correct answer)</th>
									<th scope="col" class="col-sm-1 text-center">Mark</th>
									<th scope="col" class="col-sm-1 text-center">Status</th>
									<th scope="col" class="col-sm-1"></th>
								</tr>
								</thead>
								<tbody>
								@foreach($tpQuestions as $index => $tpQuestion)
									<tr>
										<td>{{ $index + 1 }}</td>
										<td><strong>{{ $tpQuestion->question }}</strong></td>
										<td>
											<ul>
												@foreach($tpQuestion->answers as $index => $answer)
													<li class="@if($answer->ans_code == $tpQuestion->correct_answer)font-weight-bold text-success @endif">
														@if($answer->ans_code == $tpQuestion->correct_answer)
															&#10003;
														@else
															{{ ($index+1) }}
														@endif
														{{ $answer->option_text }}
													</li>
												@endforeach
											</ul>
										</td>
										<td class="text-center">{{ $tpQuestion->mark }}</td>
										<td class="text-center"><i class="fas fa-circle {{ $tpQuestion->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
										<td class="d-flex justify-content-end px-2">
											<a class="btn btn-outline-danger btn-sm mx-2 openRightModal" href="{{ route('config.editQa', ['aid' => $tpAssessment->id, 'qid' => $tpQuestion->id]) }}"><i class="fa fa-edit"></i></a>
{{--											<button type="button" data-id="{{ $tpQuestion->id }}" class="btn btn-outline-success btn-sm mx-2 editQuestion" data-toggle="tooltip" data-placement="top" title="Edit Assessment"><i class="fa fa-edit"></i></button>--}}
											<button type="button" data-id="{{ $tpQuestion->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteQuestion" data-toggle="tooltip" data-placement="top" title="Delete Assessment"><i class="fa fa-trash"></i></button>
										</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						@else
							<div class="alert alert-warning" role="alert">
								<i class="fa fa-info mx-2"></i> @lang('trainingpro::app.message.noDataFound')
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$('body').on('click', '.deleteQuestion', function() {
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
					success: function(response) {
						if (response.status == "success") {
							window.location.href = response.redirectUrl;
						}
					}
				});
			}
		});
	});
</script>
