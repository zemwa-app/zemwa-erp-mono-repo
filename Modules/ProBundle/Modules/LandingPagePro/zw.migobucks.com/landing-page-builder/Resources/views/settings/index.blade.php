@extends('layouts.app')

@push('datatable-styles')
	@include('sections.datatable_css')
@endpush

@section('content')

	<!-- SETTINGS START -->
	<div class="w-100 d-flex ">
		@if (user()->is_superadmin)
			<x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>
		@else
			<x-setting-sidebar :activeMenu="$activeSettingMenu"/>
		@endif
		<x-setting-card>
			<x-slot name="header"></x-slot>
			<!-- Add Task Export Buttons Start -->
			<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 py-4 ">
				<div id="table-actions" class="flex-grow-1 align-items-center">
					<ul class="nav nav-tabs" id="myTab" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="lpQuota-tab" data-toggle="tab" href="#lpQuota" role="tab" aria-controls="lpQuota" aria-selected="true">Landing Page Configurations</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="lpTemplate-tab" data-toggle="tab" href="#lpTemplate" role="tab" aria-controls="lpTemplate" aria-selected="false">Landing Page Templates</a>
						</li>
					</ul>
					<div class="tab-content" id="myTabContent">
						<!-- LANDING PAGE WRAPPER -->
						<div class="tab-pane fade show active" id="lpQuota" role="tabpanel" aria-labelledby="lpQuota-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-8 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-cog"></i> @lang('landingpagepro::app.header.pageTitle')</p>
									</div>
									<div class="col-sm-4">
										<button type="button" class="btn btn-block btn-primary btn-sm" id="addLimit" data-toggle="modal" data-target="#addLimitModal"><i class="fa fa-plus"></i> @lang('landingpagepro::app.button.addLimit')</button>
									</div>
								</div>
								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($packageLimits) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-6">Package Name</th>
													<th scope="col" class="col-sm-2">Page Limit</th>
													<th scope="col" class="col-sm-2">Category Limit</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($packageLimits as $index => $packageLimit)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $packageLimit->getPackage->name }}</td>
														<td>{{ $packageLimit->page_limit }}</td>
														<td>{{ $packageLimit->category_limit }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $packageLimit->id }}" class="btn btn-outline-success btn-sm mx-2 editLimit"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $packageLimit->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteLimit"><i class="fa fa-trash"></i></button>
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

						<!-- TEMPLATE WRAPPER -->
						<div class="tab-pane fade" id="lpTemplate" role="tabpanel" aria-labelledby="lpTemplate-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-9 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('landingpagepro::app.header.templateTitle')</p>
									</div>
									<div class="col-sm-3">
										<button type="button" class="btn btn-block btn-primary btn-sm" id="addTemplate" data-toggle="modal" data-target="#addTemplateModal"><i class="fa fa-plus"></i> @lang('landingpagepro::app.button.addTemlpate')</button>
									</div>
								</div>

								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($lpTemplates) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-3">Templates Name</th>
													<th scope="col" class="col-sm-2">Thumbnails</th>
													<th scope="col" class="col-sm-3">Allowed Packages</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($lpTemplates as $index => $lpTemplate)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $lpTemplate->name }}</td>
														<td>
															@if (isset($lpTemplate->thumbnail))

																<div class="image-container">
																	<img src="{{ asset(Storage::disk('public')->url($lpTemplate->thumbnail)) }}" alt="{{ $lpTemplate->name }}" class="img-thumbnail" onclick="showImageModal('{{ asset(Storage::disk('public')->url($lpTemplate->thumbnail)) }}')" style="width: auto; height: 50px; cursor: pointer;">
																</div>
																<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
																	<div class="modal-dialog modal-dialog-centered modal-xl" role="document">
																		<div class="modal-content">
																			<div class="modal-body">
																				<img src="" alt="Uploaded Image" id="modalImage" class="img-fluid">
																			</div>
																		</div>
																	</div>
																</div>
															@endif
														</td>
														<td>
															@foreach($lpTemplate->packageNames as $package)
																{{ $package }},
															@endforeach
														</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $lpTemplate->id }}" class="btn btn-outline-success btn-sm mx-2 editTemplate"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $lpTemplate->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteTemplate"><i class="fa fa-trash"></i></button>
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
					</div>
				</div>
			</div>
		</x-setting-card>
	</div>
	<!-- SETTINGS END -->

	<!-- ADD LANDING PAGE LIMIT MODAL -->
	<div class="modal fade" id="addLimitModal" data-backdrop="static" tabindex="-1" aria-labelledby="addLimitModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addLimitModalLabel">@lang('landingpagepro::app.header.addLimit')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="setLimit">
						@csrf
						<div class="form-row">
							<div class="col-4">
								<label for="package">Select Package</label>
								<select class="form-control" name="package" id="package" required>
									<option disabled>Select Package</option>

									@foreach($packages as $package)
										<option value="{{ $package->id}}">{{ $package->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-4">
								<label for="formLimit">Max. Allowed Landing Page</label>
								<input type="number" name="pageLimit" id="pageLimit" class="form-control" placeholder="Max. Allowed Landing Page" required>
							</div>
							<div class="col-4">
								<label for="formLimit">Max. Allowed Category</label>
								<input type="number" name="categoryLimit" id="categoryLimit" class="form-control" placeholder="Max. Allowed Category" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('landingpagepro::app.button.cancelLimit')</button>
					<button type="button" id="saveLimit" class="btn btn-primary">@lang('landingpagepro::app.button.saveLimit')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ADD LANDING PAGE TEMPLATE MODAL -->
	<div class="modal fade" id="addTemplateModal" data-backdrop="static" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addTemplateLabel">@lang('landingpagepro::app.header.addTemplate')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="templateForm" enctype="multipart/form-data">
						@csrf
						<input type="hidden" name="id" id="tempId" value="">
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="templateName">Template Name:</label>
								<input type="text" class="form-control" id="templateName" name="templateName" required>
							</div>
							<div class="form-group col-md-6">
								<label for="templateImage">Template Image:</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" id="templateImage" name="templateImage" accept="image/*">
									<label class="custom-file-label" for="templateImage">Choose file (Max 1MB)</label>
								</div>
							</div>
						</div>
						<div clas="form-row">
							<div class="form-group">
								<label>Select packages you want to associate with this template:</label>
								<div class="row">
									@foreach($packages as $package)
										<div class="col-md-3 my-2">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" value="{{ $package->id }}" name="packages[]" id="package-{{ $package->id }}" required>&nbsp;
												<label class="form-check-label" for="package-{{ $package->id }}">{{ $package->name }}</label>
											</div>
										</div>
									@endforeach
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('landingpagepro::app.button.cancelLimit')</button>
					<button type="button" id="saveTemplate" class="btn btn-primary">@lang('landingpagepro::app.button.saveTemlpate')</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('scripts')
	<script>
		@if (user()->is_superadmin)
		$('#saveLimit').click(function () {
			$('#saveLimit').prop('disabled', true);
			var url = "{{ route('landingpagepro-settings.store') }}";
			// Get form data
			var formData = $('#setLimit').serialize();

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
							$('#addLimitModal').modal('hide');
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
		});

		$('.editLimit').on('click', function () {
			var id = $(this).data('id');
			var url = '{{ route("landingpagepro-settings.edit", ['landingpagepro_setting' => 'placeholder']) }}'.replace('placeholder', id);

			// AJAX request
			$.ajax({
				url: url,
				type: 'get',
				success: function (response) {

					// Populate the modal fields
					$('#package option[value="' + response.package_id + '"]').prop('selected', true);
					$('#pageLimit').val(response.page_limit);
					$('#lptemplatesLimit').val(response.lptemplates_limit);

					// Show the modal
					$('#addLimitModal').modal('show');
				},
				error: function (request, status, error) {
					console.log('AJAX error:', error);
				}
			});
		});

		$('.deleteLimit').on('click', function () {
			var id = $(this).data('id');
			var token = $('meta[name="csrf-token"]').attr('content');
			var url = '{{ route("landingpagepro-settings.destroy", ['landingpagepro_setting' => 'placeholder']) }}'.replace('placeholder', id);

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
							console.error('Error:', xhr.responseText);
							Swal.fire('Error!', 'Unable to delete the record. Please try again.', 'error');
						}
					});
				} else if (result.dismiss === Swal.DismissReason.cancel) {
					// Operation cancelled by user
					Swal.fire('Cancelled', 'Your record is safe :)', 'info');
				}
			});

		});

		document.getElementById('templateImage').addEventListener('change', function (e) {
			var fileName = e.target.files[0].name;
			var nextSibling = e.target.nextElementSibling;
			nextSibling.innerText = fileName;
		});

		$('#saveTemplate').click(function () {

			var url = "{{ route('lpTemplate.store') }}";
			var csrfToken = $('meta[name="csrf-token"]').attr('content');

			// Get form data
			var formData = new FormData();
			formData.append('id', $('#tempId').val());
			formData.append('templateName', $('#templateName').val());
			formData.append('templateImage', $('#templateImage')[0].files[0]);
			$('input[name="packages[]"]:checked').each(function () {
				formData.append('packages[]', this.value);
			});

			$.ajax({
				type: 'POST',
				url: url,
				data: formData,
				contentType: false,
				processData: false,
				headers: {
					'X-CSRF-TOKEN': csrfToken
				},
				success: function (response) {
					if (response.status === 'success') {
						// If it's a success response
						Swal.fire({
							icon: 'success',
							title: 'Success!',
							text: response.message,
							confirmButtonText: 'OK'
						}).then((result) => {
							$('#addTemplateModal').modal('hide');
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
		});

		$('.editTemplate').on('click', function () {
			var id = $(this).data('id');
			var url = '{{ route("lpTemplate.edit", ['id' => 'placeholder']) }}'.replace('placeholder', id);

			// AJAX request
			$.ajax({
				url: url,
				type: 'get',
				success: function (response) {

					// Populate the modal fields
					$('#tempId').val(response.id);
					$('#templateName').val(response.templateName);
					$('input[name="packages[]"]').prop('checked', false);
					$.each(response.packages, function (index, packageId) {
						$('input[name="packages[]"][value="' + packageId + '"]').prop('checked', true);
					});

					// Show the modal
					$('#addTemplateModal').modal('show');
				},
				error: function (request, status, error) {
					console.log('AJAX error:', error);
				}
			});
		});

		$('.deleteTemplate').on('click', function () {
			var id = $(this).data('id');
			var token = $('meta[name="csrf-token"]').attr('content');
			var url = '{{ route("lpTemplate.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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
							console.error('Error:', xhr.responseText);
							Swal.fire('Error!', 'Unable to delete the record. Please try again.', 'error');
						}
					});
				} else if (result.dismiss === Swal.DismissReason.cancel) {
					// Operation cancelled by user
					Swal.fire('Cancelled', 'Your record is safe :)', 'info');
				}
			});

		});

		function showImageModal(imagePath) {
			$('#modalImage').attr('src', imagePath);
			$('#imageModal').modal('show');
		}

		@endif
	</script>
@endpush
