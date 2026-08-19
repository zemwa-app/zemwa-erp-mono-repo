@extends('layouts.app')

@push('datatable-styles')
	@include('sections.datatable_css')
@endpush

@php
	$viewLeadPermission = user()->permission('view_leadform');
	$addLeadPermission = user()->permission('add_leadform');
	$viewLeadCategory = user()->permission('view_leadformcategory');
	$addLeadCategory = user()->permission('add_leadformcategory');
	$companyId = company()->id;
@endphp

@section('content')

	<!-- CONTENT WRAPPER START -->
	<div class="content-wrapper">
		<!-- Add Task Export Buttons Start -->
		<div class="d-grid d-lg-flex d-md-flex action-bar">
			<div id="table-actions" class="flex-grow-1 align-items-center">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					@if (in_array($viewLeadPermission, ['all', 'added', 'owned', 'both']))
						<li class="nav-item">
							<a class="nav-link active" id="leadspro-tab" data-toggle="tab" href="#leadspro" role="tab" aria-controls="leadspro" aria-selected="true">Leads Pro Forms</a>
						</li>
					@endif
					@if (in_array($viewLeadCategory, ['all', 'added', 'owned', 'both']))
						<li class="nav-item">
							<a class="nav-link" id="category-tab" data-toggle="tab" href="#category" role="tab" aria-controls="category" aria-selected="false">Categories</a>
						</li>
					@endif
				</ul>
				<div class="tab-content" id="myTabContent">
					@if (in_array($viewLeadPermission, ['all', 'added', 'owned', 'both']))
						<div class="tab-pane fade show active" id="leadspro" role="tabpanel" aria-labelledby="leadspro-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-8 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('leadformspro::app.header.leadPageTitle')</p>
									</div>
									<div class="col-sm-4 d-flex justify-content-end align-items-center">
										@if ($addLeadPermission == 'all' || $addLeadPermission == 'added' || $addLeadPermission == 'owned' || $addLeadPermission == 'both')
											<button type="button" class="btn btn-primary btn-sm" id="addLimit" data-toggle="modal" data-target="#addLeadFormModal"><i class="fa fa-plus"></i> @lang('leadformspro::app.button.addLeadPage')</button>
										@endif
									</div>
									<div class="col mt-3">
										<div class="alert alert-info alert-dismissible fade show" role="alert">
											You have created <strong>{{count($leadPages) }} lead forms</strong> and you can create <strong>{{ $allowedLimits->form_limit - count($leadPages) }}</strong> more lead forms. Your maximum allowed limit is <strong>{{ $allowedLimits->form_limit }} lead forms</strong>.
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
									</div>
								</div>
								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($leadPages) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-3">Lead Form Name</th>
													<th scope="col" class="col-sm-2">Category</th>
													<th scope="col" class="col-sm-3">Sharable Links<br><small>Click on the link to copy the url</small></th>
													<th scope="col" class="col-sm-3">Action</th>
												</tr>
												</thead>
												<tbody>
												@foreach($leadPages as $index => $leadPage)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $leadPage->name }}</td>
														<td>{{ $leadPage->category->name }}</td>
														<td class="px-2">
															<button type="button" data-toggle="tooltip" data-placement="bottom" title="Copy direct link." data-url="{{ route('front.lead_pro_form', [encrypt($leadPage->id)]).'?styled=1' }}" class="btn btn-outline-info btn-sm mx-2 copy-button"><i class="fa fa-link"></i></button>
															<button type="button" data-toggle="tooltip" data-placement="bottom" title="Copy direct link with logo." data-url="{{ route('front.lead_pro_form', [encrypt($leadPage->id)]).'?styled=1&with_logo=1' }}" class="btn btn-outline-info btn-sm mx-2 copy-button"><i class="fa fa-link"></i></button>
															<button type="button" data-toggle="tooltip" data-placement="bottom" title="Copy iFrame code." data-url="&lt;iframe src='{{ route('front.lead_pro_form',[encrypt($leadPage->id)]) }}' frameborder='0' scrolling='yes' style='display:block; width:100%; height:60vh;'>&lt;/iframe&gt;" class="btn btn-outline-info btn-sm mx-2 copy-button"><i class="fa fa-globe"></i></button>
														</td>
														<td class="px-2">
															<button type="button" data-id="{{ $leadPage->id }}" class="btn btn-outline-success btn-sm mx-2 editLeadForm"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $leadPage->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteLeadForm"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										@else
											<div class="alert alert-warning" role="alert">
												<i class="fa fa-info mx-2"></i> @lang('leadformspro::app.message.noDataFound')
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					@endif
					@if (in_array($viewLeadCategory, ['all', 'added', 'owned', 'both']))
						<div class="tab-pane fade" id="category" role="tabpanel" aria-labelledby="category-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-8 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('leadformspro::app.header.leadCategoriesTitle')</p>
									</div>
									<div class="col-sm-4 d-flex justify-content-end align-items-center">
										@if ($addLeadCategory == 'all' || $addLeadCategory == 'added' || $addLeadCategory == 'owned' || $addLeadCategory == 'both')
											<button type="button" class="btn btn-primary btn-sm" id="addLeadCategory" data-toggle="modal" data-target="#addLeadCategoryFormModal"><i class="fa fa-plus"></i> @lang('leadformspro::app.button.addLeadCategory')</button>
										@endif
									</div>
									<div class="col mt-3">
										<div class="alert alert-info alert-dismissible fade show" role="alert">
											You have created <strong>{{ count($leadCategories) }} lead categories</strong> and you can create <strong>{{ $allowedLimits->category_limit - count($leadCategories)}}</strong> more lead categories. Your maximum allowed limit is <strong>{{ $allowedLimits->category_limit }} lead categories</strong>.
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
									</div>
								</div>
								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($leadCategories) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-6">Category Name</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($leadCategories as $index => $leadCategory)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $leadCategory->name }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $leadCategory->id }}" class="btn btn-outline-success btn-sm mx-2 editLeadCategory"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $leadCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteLeadCategory"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										@else
											<div class="alert alert-warning" role="alert">
												<i class="fa fa-info mx-2"></i> @lang('leadformspro::app.message.noDataFound')
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
	<!-- CONTENT WRAPPER END -->
	<!-- ADD LEAD PAGE -->
	@if (in_array($addLeadPermission, ['all', 'added', 'owned', 'both']))
		<div class="modal fade" id="addLeadFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addLeadFormModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addLimitModalLabel">@lang('leadformspro::app.header.addNewLeadPage')</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="createLeadForms">
							@csrf
							<input type="hidden" name="leadFormId" id="leadFormId" class="form-control form-control-lg" value="">
							<div class="row p-3 pt-4 border-bottom">
								<div class="col-md-6 px-2">Lead Form Name</div>
								<div class="col-md-6 px-2">
									<input type="text" name="leadFormName" id="leadFormName" class="form-control" placeholder="Lead Form Name" required>
								</div>
							</div>
							<div class="row p-3 pt-4 border-bottom">
								<div class="col-md-6 px-2">Select Category</div>
								<div class="col-md-6 px-2">
									<select class="form-control" name="leadCat" id="leadCat" required>
										<option disabled>Select Lead Category</option>
										@foreach($leadCategories as $leadCategory)
											<option value="{{ $leadCategory->id }}">{{ $leadCategory->name }}</option>
										@endforeach
									</select>
								</div>
							</div>
							@foreach ($leadFormFields as $item)
								<div class="row p-3 pt-4 border-bottom">
									<div class="col-md-6 px-2">{{ $item->field_display_name}}</div>
									<div class="col-md-6 px-2">
										@if ($item->field_name != 'name')
											<div class="custom-control custom-switch">
												<input type="checkbox" class="custom-control-input change-setting" data-setting-id="{{ $item->id }}" id="{{ $item->id }}">
												<label class="custom-control-label f-14 cursor-pointer" for="{{ $item->id }}"></label>
											</div>
										@else
											--
										@endif
									</div>
								</div>
							@endforeach
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('leadformspro::app.button.cancelLimit')</button>
						<button type="button" id="saveLeadForm" class="btn btn-primary">@lang('leadformspro::app.button.saveLeadPage')</button>
					</div>
				</div>
			</div>
		</div>
	@endif

	<!-- ADD LEAD CATEGORY -->
	@if (in_array($addLeadCategory, ['all', 'added', 'owned', 'both']))
		<div class="modal fade" id="addLeadCategoryFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addLeadCategoryFormModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addCategoryModalLabel">@lang('leadformspro::app.header.addLeadCategoryTitle')</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="createLeadCategory">
							@csrf
							<div class="form-row">
								<div class="col-12">
									<input type="hidden" name="leadCatId" id="leadCatId" class="form-control form-control-lg" value="">
									<input type="text" name="leadCatName" id="leadCatName" class="form-control form-control-lg" placeholder="Enter Category Name" value="" minlength="3" required>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('leadformspro::app.button.cancelLimit')</button>
						<button type="button" id="saveLeadCategory" class="btn btn-primary">@lang('leadformspro::app.button.saveLeadCategory')</button>
					</div>
				</div>
			</div>
		</div>
	@endif

@endsection

@push('scripts')
	<script>
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

		@if (in_array($addLeadPermission, ['all', 'added', 'owned', 'both']))
		$('#addLeadFormModal').on('hidden.bs.modal', function () {
			$('#createLeadForms').trigger('reset');
		});

		$('#saveLeadForm').on('click', function () {
			var saveButton = $('#saveLeadForm');
			var url = "{{ route('leadProForm.store') }}";

			saveButton.prop('disabled', true); // Disable button
			saveButton.html('<i class="fas fa-spinner fa-spin"></i> Processing...');

			var formData = {
				checkboxes: []
			};

			document.querySelectorAll('.change-setting').forEach(function (checkbox) {
				formData.checkboxes.push({
					settingId: checkbox.getAttribute('data-setting-id'),
					checked: checkbox.checked
				});
			});

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					_token: "{{ csrf_token() }}",
					id: document.getElementById('leadFormId').value,
					leadFormName: document.getElementById('leadFormName').value,
					leadCat: document.getElementById('leadCat').value,
					checkboxes: formData.checkboxes,
				},
				success: function (response) {
					console.log('found error 0');
					if (response.status === 'success') {
						// If it's a success response
						Swal.fire({
							icon: 'success',
							title: 'Success!',
							text: response.message,
							confirmButtonText: 'OK'
						}).then((result) => {
							$('#addLeadFormModal').modal('hide');
							// Reload the page after the user clicks OK
							if (result.isConfirmed) {
								location.reload();
							}
						});
					} else {
						// Handle unexpected success response
						console.error('Unexpected success response:', response);
					}
					saveButton.prop('disabled', false); // Enable button
					saveButton.html('Save Lead Page'); // Revert button text
				},
				error: function (error) {
					if (error.status === 422 || error.status === 403) {
						// If it's a validation error
						var errorResponse = JSON.parse(error.responseText);
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: errorResponse.message,
							confirmButtonText: 'OK'
						});
					} else {
						// If it's any other error
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: 'An unexpected error occurred.',
							confirmButtonText: 'OK'
						});
						console.error('Unexpected error:', error);
					}
					saveButton.prop('disabled', false); // Enable button
					saveButton.html('Save Lead Page'); // Revert button text
				}
			});

		});

		$('.editLeadForm').on('click', function () {
			var id = $(this).data('id');
			var url = '{{ route("leadProForm.edit", ["id" => "placeholder"]) }}'.replace('placeholder', id);

			$('.preloader-container').addClass('d-flex').css('display', 'block');

			// AJAX request
			$.ajax({
				url: url,
				type: 'get',
				success: function (response) {

					// Populate the modal fields
					$('#leadFormId').val(response.id);
					$('#leadFormName').val(response.name);
					$('#leadCat').val(response.leadCat);

					// Reset all checkboxes to unchecked state
					$('.custom-control-input.change-setting').prop('checked', false);

					// Iterate over each object in the response.fields array
					if (Array.isArray(response.fields)) {
						response.fields.forEach(function (field) {
							$('.custom-control-input.change-setting[data-setting-id="' + field.settingId + '"]')
								.prop('checked', field.checked === 'true');
						});
					}
					$('.preloader-container').removeClass('d-flex').css('display', 'none');
					// Show the modal
					$('#addLeadFormModal').modal('show');
				},
				error: function (request, status, error) {
					console.log('AJAX error:', error);
					$('.preloader-container').removeClass('d-flex').css('display', 'none');
				}
			});
		});

		$('.deleteLeadForm').on('click', function () {
			var id = $(this).data('id');
			var token = $('meta[name="csrf-token"]').attr('content');
			var url = '{{ route("leadProForm.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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
					$('.preloader-container').addClass('d-flex').css('display', 'block');
					$.ajax({
						url: url,
						type: 'POST',
						data: {
							_method: 'DELETE',
							_token: token
						},
						success: function (response) {
							$('.preloader-container').removeClass('d-flex').css('display', 'none');
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
							$('.preloader-container').removeClass('d-flex').css('display', 'none');
							Swal.fire('Error!', 'Unable to delete the record. Please try again.', 'error');
						}
					});
				} else if (result.dismiss === Swal.DismissReason.cancel) {
					// Operation cancelled by user
					Swal.fire('Cancelled', 'Your record is safe :)', 'info');
				}
			});

		});
		@endif

		@if (in_array($addLeadCategory, ['all', 'added', 'owned', 'both']))
		$('#addLeadCategoryFormModal').on('hidden.bs.modal', function () {
			$('#createLeadCategory').trigger('reset');
		});

		$('#saveLeadCategory').click(function () {
			var saveButton = $('#saveLeadCategory');
			var url = "{{ route('lfpLeadCategory.store') }}";
			// Get form data
			var formData = $('#createLeadCategory').serialize();

			saveButton.prop('disabled', true); // Disable button
			saveButton.html('<i class="fas fa-spinner fa-spin"></i> Processing...');

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
							$('#addLeadCategoryFormModal').modal('hide');
							// Reload the page after the user clicks OK
							if (result.isConfirmed) {
								location.reload();
							}
						});
					} else {
						// Handle unexpected success response
						console.error('Unexpected success response:', response);
					}
					saveButton.prop('disabled', false); // Enable button
					saveButton.html('Save Lead Category'); // Revert button text
				},
				error: function (error) {
					if (error.status === 422 || error.status === 403) {
						// If it's a validation error
						var errorResponse = JSON.parse(error.responseText);
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: errorResponse.message,
							confirmButtonText: 'OK'
						});
					} else {
						// If it's any other error
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: 'An unexpected error occurred.',
							confirmButtonText: 'OK'
						});
						console.error('Unexpected error:', error);
					}
					saveButton.prop('disabled', false); // Enable button
					saveButton.html('Save Lead Category'); // Revert button text
				}
			});
		});

		$('.editLeadCategory').on('click', function () {
			var id = $(this).data('id');
			var url = '{{ route("lfpLeadCategory.edit", ["id" => "placeholder"]) }}'.replace('placeholder', id);

			$('.preloader-container').addClass('d-flex').css('display', 'block');

			// AJAX request
			$.ajax({
				url: url,
				type: 'get',
				success: function (response) {

					// Populate the modal fields
					$('#leadCatId').val(response.id);
					$('#leadCatName').val(response.name);
					$('.preloader-container').removeClass('d-flex').css('display', 'none');
					// Show the modal
					$('#addLeadCategoryFormModal').modal('show');
				},
				error: function (request, status, error) {
					console.log('AJAX error:', error);
					$('.preloader-container').removeClass('d-flex').css('display', 'none');
				}
			});
		});

		$('.deleteLeadCategory').on('click', function () {
			var id = $(this).data('id');
			var token = $('meta[name="csrf-token"]').attr('content');
			var url = '{{ route("lfpLeadCategory.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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
					$('.preloader-container').addClass('d-flex').css('display', 'block');
					$.ajax({
						url: url,
						type: 'POST',
						data: {
							_method: 'DELETE',
							_token: token
						},
						success: function (response) {
							$('.preloader-container').removeClass('d-flex').css('display', 'none');
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
							$('.preloader-container').removeClass('d-flex').css('display', 'none');
							Swal.fire('Error!', 'Unable to delete the record. Please try again.', 'error');
						}
					});
				} else if (result.dismiss === Swal.DismissReason.cancel) {
					// Operation cancelled by user
					Swal.fire('Cancelled', 'Your record is safe :)', 'info');
				}
			});

		});
		@endif

	</script>
@endpush
