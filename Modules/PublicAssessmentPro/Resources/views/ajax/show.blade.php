@php

@endphp
<div id="notice-detail-section">
	<div class="row">
		<div class="col-sm-12">
			<div class="card bg-white border-0 b-shadow-4">
				<div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
					<div class="row">
						<div class="col-lg-10 col-10">
							<h4 class="heading-h4">@lang('publicassessmentpro::app.header.qaListingHeader') "{{ $paAssessment->assessment_name }}"</h4>
						</div>
						<div class="col-lg-2 col-2 text-right"><!--space for button/dropdown menu --></div>
					</div>
				</div>
				<div class="card-body">
					<div class="col table-responsive">
						@ if (count($tpQuestions) != 0)
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
								@ foreach($tpQuestions as $index => $tpQuestion)
									<tr>
										<td>{ { $index + 1 }}</td>
										<td><strong>{ { $tpQuestion->question }}</strong></td>
										<td>
											<ul>
												@ foreach($tpQuestion->answers as $index => $answer)
													<li class="@ if($answer->ans_code == $tpQuestion->correct_answer)font-weight-bold text-success @ endif">
														@ if($answer->ans_code == $tpQuestion->correct_answer)
															&#10003;
														@ else
															{ { ($index+1) }}
														@ endif
														{ { $answer->option_text }}
													</li>
												@ endforeach
											</ul>
										</td>
										<td class="text-center">{ { $tpQuestion->mark }}</td>
										<td class="text-center"><i class="fas fa-circle { { $tpQuestion->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
										<td class="d-flex justify-content-end px-2">
											<a class="btn btn-outline-danger btn-sm mx-2 openRightModal" href="{ { route('config.editQa', ['aid' => $paAssessment->id, 'qid' => $tpQuestion->id]) }}"><i class="fa fa-edit"></i></a>
{{--											<button type="button" data-id="{{ $tpQuestion->id }}" class="btn btn-outline-success btn-sm mx-2 editQuestion" data-toggle="tooltip" data-placement="top" title="Edit Assessment"><i class="fa fa-edit"></i></button>--}}
											<button type="button" data-id="{ { $tpQuestion->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteQuestion" data-toggle="tooltip" data-placement="top" title="Delete Assessment"><i class="fa fa-trash"></i></button>
										</td>
									</tr>
								@ endforeach
								</tbody>
							</table>
						@ else
							<div class="alert alert-warning" role="alert">
								<i class="fa fa-info mx-2"></i> @lang('publicassessmentpro::app.message.noDataFound')
							</div>
						@ endif
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="addCategoryFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addCategoryFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryFormModalLabel">@ lang('publicassessmentpro::app.header.addCategory')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    @csrf
                    <div class="form-row">
                        <div class="col-8 form-group my-3">
                            <input type="hidden" name="tpCategoryId" id="tpCategoryId" value="">
                            <label class="f-14 text-dark-grey mb-12" data-label="true" for="tpCategory">Category Name<sup class="f-14 mr-1">*</sup></label>
                            <input type="text" id="tpCategory" class="form-control height-35 f-14" placeholder="e.g. On-boarding" value="" name="tpCategory" autocomplete="off" required>
                        </div>
                        <div class="col-4 form-group my-3 d-flex align-items-end justify-content-center">
                            <div class="custom-control custom-switch d-flex align-items-center justify-content-center">
                                <span class="mr-2 deactivate-text">Deactivate</span>
                                <div class="custom-control custom-switch">
                                    <input id="tpStatus" class="custom-control-input" type="checkbox" name="tpStatus" value="">
                                    <label class="custom-control-label" for="tpStatus">Activate</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-12 form-group my-3">
                            <label class="f-14 text-dark-grey mb-12" data-label="true" for="tpDescription">Category Description<sup class="f-14 mr-1">*</sup></label>
                            <input type="text" id="tpDescription" class="form-control height-35 f-14" placeholder="e.g. Assessment category for on-boarding employees" value="" name="tpDescription" autocomplete="off" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('publicassessmentpro::app.button.cancelLimit')</button>
                <button type="button" id="btnSaveCategory" class="btn btn-primary">@lang('publicassessmentpro::app.button.saveCategory')</button>
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
