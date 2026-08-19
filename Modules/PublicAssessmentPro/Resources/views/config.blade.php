@extends('layouts.app')

@push('datatable-styles')

@endpush

@php
	$companyId = company()->id;
@endphp

@section('content')
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

	<!-- CONTENT WRAPPER START -->
	<div class="content-wrapper">
		<!-- Add Task Export Buttons Start -->
		<div class="d-grid d-lg-flex d-md-flex action-bar">
			<div id="table-actions" class="flex-grow-1 align-items-center">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="assessment-tab" data-toggle="tab" href="#assessment" role="tab" aria-controls="assessment" aria-selected="true">Assessments</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="assignhub-tab" data-toggle="tab" href="#qamanagement" role="tab" aria-controls="qamanagement" aria-selected="false">Question Management</a>
					</li>
					<!-- <li class="nav-item">
						<a class="nav-link" id="configurator-tab" data-toggle="tab" href="#configurator" role="tab" aria-controls="configurator" aria-selected="false">Configurator</a>
					</li> -->
				</ul>
				<div class="tab-content" id="myTabContent">
                    {{--Assessment tab --}}
					<div class="tab-pane fade show active" id="assessment" role="tabpanel" aria-labelledby="assessment-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-10 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('publicassessmentpro::app.header.assessmentTitle')</p>
								</div>
								<div class="col-sm-2">
									<button type="button" class="btn btn-outline-danger btn-sm" id="addAssessment" data-toggle="modal" data-target="#addAssessmentFormModal"><i class="fa fa-plus"></i> @lang('publicassessmentpro::app.button.addAssessment')</button>
								</div>
							</div>
                            <div class="row my-2">
                                <div class="col mt-3">
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        You have created <strong>{{ $assessmentCount }} Assessments</strong> and you can create <strong>{{ $alocatedCount - $assessmentCount }}</strong> more Assessments. Your maximum allowed limit is <strong>{{ $alocatedCount }} Assessments</strong>.
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

							<div class="row my-2 p-3">
								<div class="col table-responsive">
									@if(count($paAssessments) != 0)
										<table class="table">
                                            	<thead class="">
											<tr>
												<th scope="col" class="col-sm-1">#</th>
												<th scope="col" class="col-sm-3">Assessment Name</th>
												<th scope="col" class="col-sm-1 text-center">Min. Score</th>
												<th scope="col" class="col-sm-1 text-center">Max. Score</th>
												<th scope="col" class="col-sm-2">Sharable Links<br><small>Click on the link to copy the url</small></th>
												<th scope="col" class="col-sm-1 text-center">Submission Limit</th>
												<th scope="col" class="col-sm-1 text-center">Status</th>
												<th scope="col" class="col-sm-2"></th>
											</tr>
											</thead>
											<tbody>
											@foreach($paAssessments as $index => $paAssessment)
													<td>{{ $index + 1 }}</td>
													<td>

															<strong>{{ $paAssessment->assessment_name }}</strong>
														{{--<br>
														<span class="small text-info">
															{{ $paAssessment->products->name }}

														</span>--}}
													</td>
													<td class="text-center">{{ $paAssessment->min_score }}</td>
													<td class="text-center">{{ $paAssessment->max_score }}</td>
													<td class="px-2">
														<button type="button" data-toggle="tooltip" data-placement="bottom" title="Copy direct link." data-url="{{ route('public-assessment', [encrypt($paAssessment->id)]) }}" class="btn btn-outline-info btn-sm mx-2 copy-button"><i class="fa fa-link"></i></button>
													</td>
													<td class="text-center">{{ $paAssessment->submission_limit == 0 ? '-NA-' : $paAssessment->submission_limit }}</td>
													<td class="text-center"><i class="fas fa-circle {{ $paAssessment->status ? 'text-success' : 'text-danger' }}"></i></td>
													<td class="d-flex justify-content-end px-2">

														<button type="button" data-id="{{ $paAssessment->id }}" class="btn btn-outline-success btn-sm mx-2 editAssessment" data-toggle="tooltip" data-placement="top" title="Edit Assessment"><i class="fa fa-edit"></i></button>
														<button type="button" data-id="{{ $paAssessment->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteAssessment" data-toggle="tooltip" data-placement="top" title="Delete Assessment"><i class="fa fa-trash"></i></button>
													</td>
												</tr>
											@endforeach
											</tbody>
										</table>
									@else
										<div class="alert alert-warning" role="alert">
											<i class="fa fa-info mx-2"></i> @lang('publicassessmentpro::app.message.noDataFound')
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>

                    {{--Question Management--}}
					<div class="tab-pane fade" id="qamanagement" role="tabpanel" aria-labelledby="qamanagement-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
                                <div class="col-sm-9 d-flex align-items-center">
                                    <p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('publicassessmentpro::app.header.qaManagementTitle')</p>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control select-picker" name="papAssessmentSelect" id="papAssessmentSelect" data-live-search="false" data-size="8">
                                        <option>-Select Assessment-</option>
                                        @if(count($papAssessments) != 0)
                                            @foreach($papAssessments as $papAssessment)
                                                <option value="{{ $papAssessment->id }}">{{ $papAssessment->assessment_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
							</div>
							<div class="row my-2 p-3">
                                <div class="container" id="selectedAssess">{{-- Load Data Table --}}</div>
							</div>
						</div>
					</div>

                    {{--configurator--}}
					<div class="tab-pane fade" id="configurator" role="tabpanel" aria-labelledby="configurator-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-12 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('publicassessmentpro::app.header.configuratorTitle')</p>
								</div>
							</div>
							<div class="row my-2 p-3">
								<div class="col table-responsive">
									<div class="container-fluid">
										<div class="row">
											<div class="col-5 px-0 mt-3">
												<!-- CATEGORY WRAPPER START -->
												<div class="col-12 px-1 mt-3">
													<div class="d-flex align-items-center justify-content-between bg-light border-bottom border-secondary p-2">
														<h4 class="mb-0">@lang('publicassessmentpro::app.header.categories'):</h4>
														<button type="button" class="btn text-dark btn-sm" id="addCategory" data-toggle="modal" data-target="#addCategoryFormModal">
															<i class="fa fa-plus"></i> @lang('publicassessmentpro::app.button.addCategory')
														</button>
													</div>
													<div class="col table-responsive p-0">
														@ if(count($tpCategories) != 0)
															<table class="table">
																<thead class="">
																<tr>
																	<th scope="col" class="col-sm-1">#</th>
																	<th scope="col" class="col-sm-7">Name/Description</th>
																	<th scope="col" class="col-sm-1 text-center">Status</th>
																	<th scope="col" class="col-sm-3"></th>
																</tr>
																</thead>
																<tbody>
																@ foreach($tpCategories as $index => $tpCategory)
																	<tr>
																		<td>{ { $index + 1 }}</td>
																		<td>
																			<strong>{ { $tpCategory->name }}</strong><br>
																			<small>{ { $tpCategory->description }}</small>
																		</td>
																		<td class="text-center"><i class="fas fa-circle { { $tpCategory->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
																		<td class="d-flex justify-content-end px-2">
																			<button type="button" data-id="{ { $tpCategory->id }}" class="btn btn-outline-success btn-sm mx-2 editCategory"><i class="fa fa-edit"></i></button>
																			<button type="button" data-id="{ { $tpCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteCategory"><i class="fa fa-trash"></i></button>
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
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- CONTENT WRAPPER END -->

	<!-- ADD ASSESSMENT MODAL -->
	<div class="modal fade" id="addAssessmentFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addAssessmentFormModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addAssessmentFormModalLabel">@lang('publicassessmentpro::app.header.addAssessment')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="addAssessmentForm">
						@csrf
						<div class="form-row">
							<div class="col-4 form-group my-3">
								<input type="hidden" name="tpAssessmentId" id="tpAssessmentId" value="">
								<div class="select-box py-2 px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="paAssessProduct">Select Product<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="paAssessProduct" id="paAssessProduct" data-live-search="false" data-size="8">
										<option>--</option>
                                        <option value="0">Other</option>
										@if(count($paProducts) != 0)
											@foreach($paProducts as $paProduct)
												<option value="{{ $paProduct->id }}">{{ $paProduct->name }}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="col-5 form-group my-3">
								<div class="select-box py-2 px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="paAssessType">Select Assessment Type<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="paAssessType" id="paAssessType" data-live-search="false" data-size="8">
										<option value="0">Score Based</option>
                                        <option value="1">Scoreless</option>
                                        <option value="2">Rating Based</option>
									</select>
								</div>
							</div>

							<div class="col-3 form-group my-3 d-flex align-items-end justify-content-center">
								<div class="custom-control custom-switch d-flex align-items-center justify-content-center">
									<span class="mr-2 deactivate-text">Deactivate</span>
									<div class="custom-control custom-switch">
										<input id="tpAssessStatus" class="custom-control-input" type="checkbox" name="tpAssessStatus" value="">
										<label class="custom-control-label" for="tpAssessStatus">Activate</label>
									</div>
								</div>
							</div>
						</div>
						<!--load partial blades upon assessment type -->
                        <div id="viewForAssessmentType">
							{{--Fields from partial view--}}
						</div>

						<div class="form-row">
							<div class="col-9 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessDescription">Assessment Description<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpAssessDescription" class="form-control height-35 f-14" placeholder="e.g. Basic assessment for new resources" value="" name="tpAssessDescription" autocomplete="off" required>
							</div>
							<div class="col-3 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="paAssessSubLimit">Enter Submission Limit<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="paAssessSubLimit" class="form-control height-35 f-14" placeholder="e.g. 0" value="" name="paAssessSubLimit" autocomplete="off" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer d-flex justify-content-between">
					<span class="modal-footer-info text-info small">
						<i class="fas fa-info-circle fa-lg rounded-circle"></i> Instruction:<br>
						Use '0' (numeric zero) for no submission restriction.
					</span>
					<div class="d-flex">
						<button type="button" class="btn btn-secondary btn-sm mr-1" data-dismiss="modal">@lang('publicassessmentpro::app.button.cancelLimit')</button>
						<button type="button" id="btnSaveAssessment" class="btn btn-primary btn-sm">@lang('publicassessmentpro::app.button.saveAssessment')</button>
					</div>
				</div>
			</div>
		</div>
	</div>


@endsection

@push('scripts')
	<script>
		$(document).ready(function () {
			assessTypeFields(0);
			$(document).on('click', '#addAssessment', function () {
                $('#addAssessmentForm')[0].reset();
				refreshSelectPicker('paAssessProduct');
				refreshSelectPicker('paAssessType');

            });
			$('#btnSaveAssessment').click(function () {
				var url = "{{ route('publicassessmentpro.config.createAssessment') }}";
				var form = $('#addAssessmentForm')[0];
				if (form.checkValidity()) {
					// Get form data
					var formData = $('#addAssessmentForm').serialize();
					if (!$("#tpAssessStatus").is(":checked")) {
						formData += "&tpAssessStatus=0";
					} else {
						formData += "&tpAssessStatus=1";
					}

					$.ajax({
						type: 'POST',
						url: url,
						data: formData,
						success: function (response) {
							if (response.status === 'success') {
								// If it's a success response
								Swal.fire({
									icon: 'success',
									title: 'Success!',
									text: response.message,
									confirmButtonText: 'OK'
								}).then((result) => {
									$('#addAssessmentFormModal').modal('hide');
									// Reload the page after the user clicks OK
									if (result.isConfirmed) {
										location.reload();
									}
								});
							} else {
								// Handle unexpected success response
								console.error('Unexpected success response:', response);
							}
						},
						error: function (error) {
							if (error.status === 422) {
								// If it's a validation error
								var errorResponse = error.responseJSON;
								var errorMessage = '';

								// Construct a message from the validation errors
								$.each(errorResponse.message, function (key, value) {
									errorMessage += value[0] + '\n';
								});

								Swal.fire({
									icon: 'warning',
									title: 'Validation Error!',
									text: errorMessage,
									confirmButtonText: 'OK'
								});
							} else {
								// If it's any other error
								Swal.fire({
									icon: 'error',
									title: 'Error!',
									text: 'An error occurred. Please try again.',
									confirmButtonText: 'OK'
								});
							}
						}
					});
				} else {
					form.reportValidity();
				}
			});



			$('.editAssessment').on('click', function () {
				var id = $(this).data('id');
				var url = '{{ route("publicassessmentpro.config.editAssessment", ['id' => 'placeholder']) }}'.replace('placeholder', id);
				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					dataType: 'json',
					success: function (response) {
						assessTypeFields(response.data.paAssessType).then( function(success) {
							if (success) {
								// Populate the modal only after assessTypeFields succeeds
								$('#tpAssessmentId').val(response.data.tpAssessmentId);
								$('#paAssessProduct').val(response.data.paAssessProduct);
								$('#paAssessType').val(response.data.paAssessType);
								$('#tpAssessment').val(response.data.tpAssessment);
								$('#tpAssessMaxScore').val(response.data.tpAssessMaxScore);
								$('#tpAssessMinScore').val(response.data.tpAssessMinScore)
								$('#tpAssessDescription').val(response.data.tpAssessDescription);
								$('#paAssessSubLimit').val(response.data.paAssessSubLimit);
								$('#tpAssessStatus').prop('checked', response.data.tpAssessStatus === 1);
								refreshSelectPicker('paAssessProduct');
								refreshSelectPicker('paAssessType');
								// Show the modal
								$('#addAssessmentFormModal').modal('show');
							} else {
								// Handle the case where assessTypeFields fails
								console.log('AJAX error:', error);
							}
						});
					},
					error: function (request, status, error) {

					}
				});
			});
			$('.deleteAssessment').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("publicassessmentpro.config.destroyAssessment", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				Swal.fire({
					title: 'Are you sure?',
					text: 'Once deleted, you will not be able to recover this record!',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Delete',
					cancelButtonText: 'Cancel',
					confirmButtonColor: '#d33',
					cancelButtonColor: '#3085d6',
					reverseButtons: true,
					dangerMode: true,
				}).then((result) => {
					if (result.value) {  // Check if the confirm button was clicked
						$.ajax({
							url: url,
							type: 'POST',
							data: {
								_method: 'DELETE',
								_token: token
							},
							success: function (response) {
								Swal.fire({
									icon: 'success',
									title: 'Success!',
									text: response.success,
									confirmButtonText: 'OK'
								}).then((result) => {
									if (result.isConfirmed) {
										location.reload();
									}
								});
							},
							error: function (xhr) {
								console.error('Error:', xhr);
								var errorMessage = 'Unable to delete the record. Please try again.';
								try {
									var errorResponse = JSON.parse(xhr.responseText);
									if (errorResponse.error) {
										errorMessage = errorResponse.error;
									}
								} catch (e) {
								}
								Swal.fire('Error!', errorMessage, 'error');
							}
						});
					} else if (result.dismiss === Swal.DismissReason.cancel) {
						Swal.fire('Cancelled', 'Your record is safe :)', 'info');
					}
				});

			});
		

            $(document).on('change', '#paAssessType', function () {
                const selectedTypeId = $(this).val();
                assessTypeFields(selectedTypeId);
            });
			
			


			function assessTypeFields(typyId=0){
				return new Promise((resolve, reject) => { // Wrap the AJAX call in a Promise
					$("#viewForAssessmentType").html('');
					var token = $('meta[name="csrf-token"]').attr('content');
					var url = '{{ route("publicassessmentpro.config.getAssessTypeFields", ["id" => ":placeholder"]) }}';
					url = url.replace(':placeholder', typyId);

					$.ajax({
						url: url,
						type: 'GET',
						success: function(response) {
							$("#viewForAssessmentType").html(response.html);
							resolve(true); // Resolve the promise with success status
						},
						error: function(request, status, error) {
							console.log('AJAX error:', error);
							reject(false); // Reject the promise with error status
						}
					});
				});
			}


        		$(document).on('change', '#papAssessmentSelect', function () {
                const selectedAssessmentId = $(this).val();
                var token = $('meta[name="csrf-token"]').attr('content');
                var url = '{{ route("publicassessmentpro.config.getAssessQuestion", ['id' => 'placeholder']) }}'.replace('placeholder', selectedAssessmentId);

                // AJAX request
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        //console.log(response.html);
                        $("#selectedAssess").html(response.html)
                    },
                    error: function (request, status, error) {
                        console.log('AJAX error:', error);
                    }
                });
            });

			// Function to refresh Bootstrap SelectPicker
			function refreshSelectPicker(selectId) {
				$('#' + selectId).selectpicker('refresh');
			}


			$(".deactivate-text").click(function () {
				$("#tpStatus").click();
			});

			$('#myTab a:first').tab('show');
			// Retrieve the last active tab from localStorage
			var lastActiveTab = localStorage.getItem('lastActiveTab');
			if (lastActiveTab) {
				// Activate the last active tab
				$('#myTab a[href="' + lastActiveTab + '"]').tab('show');
			}
			// Save the last active tab to localStorage when a tab is clicked
			$('#myTab a').on('click', function (e) {
				localStorage.setItem('lastActiveTab', $(this).attr('href'));
			});
		});
		
		document.querySelectorAll('.copy-button').forEach(button => {
			button.addEventListener('click', function () {
				const url = this.getAttribute('data-url');
				navigator.clipboard.writeText(url).then(() => {
					Swal.fire({
						title: '',
						text: 'URL copied to clipboard!',
						timer: 2000,
						showConfirmButton: false,
						willClose: () => {
							console.log('Alert closed');
						}
					});
				}).catch(err => {
					console.error('Error copying text: ', err);
				});
			});
		});
	</script>
@endpush
