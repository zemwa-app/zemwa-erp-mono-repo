@extends('layouts.app')

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

			<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 py-4 ">
				@if (user()->is_superadmin)

					<div class="row">

						<div class="col-md-12 mb-2">

							<div class="row my-2">
								<div class="col-sm-9 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('leadformspro::app.header.pageTitle')</p>
								</div>
								<div class="col-sm-3">
									<button type="button" class="btn btn-block btn-primary btn-sm" id="addLimit" data-toggle="modal" data-target="#addLimitModal"><i class="fa fa-plus"></i> @lang('leadformspro::app.button.addLimit')</button>
								</div>
							</div>

							<div class="row my-2 p-3">
								<div class="col table-responsive">
									@if(count($excludedPackageIds) != 0)
										<table class="table">
											<thead class="">
											<tr>
												<th scope="col" class="col-sm-1">#</th>
												<th scope="col" class="col-sm-6">Package Name</th>
												<th scope="col" class="col-sm-2">Form Limit</th>
												<th scope="col" class="col-sm-2">Category Limit</th>
												<th scope="col" class="col-sm-3"></th>
											</tr>
											</thead>
											<tbody>
											@foreach($lfpLFSs as $index => $lfpLFS)
												<tr>
													<td>{{ $index + 1 }}</td>
													<td>{{ $lfpLFS->getPackage->name }}</td>
													<td>{{ $lfpLFS->form_limit }}</td>
													<td>{{ $lfpLFS->category_limit }}</td>
													<td class="d-flex justify-content-end px-2">
														<button type="button" data-id="{{ $lfpLFS->id }}" class="btn btn-outline-success btn-sm mx-2 editLimit"><i class="fa fa-edit"></i></button>
														<button type="button" data-id="{{ $lfpLFS->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteLimit"><i class="fa fa-trash"></i></button>
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

				@else
					<div class="col-lg-12 py-5">
						<div class="w-100 h-100 d-flex align-items-center justify-content-center">
							<div>
								Not Authorised
							</div>
						</div>
					</div>
				@endif
			</div>

		</x-setting-card>

	</div>
	<!-- SETTINGS END -->

	<!-- ADD LEAD FORM LIMIT -->
	<div class="modal fade" id="addLimitModal" data-backdrop="static" tabindex="-1" aria-labelledby="addLimitModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addLimitModalLabel">@lang('leadformspro::app.header.addLimit')</h5>
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
									@foreach($lfpPackages as $lfpPackage)
										<option value="{{ $lfpPackage->id }}">{{ $lfpPackage->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-4">
								<label for="formLimit">Max. Allowed Forms</label>
								<input type="number" name="formLimit" id="formLimit" class="form-control" placeholder="Max. Allowed Forms" required>
							</div>
							<div class="col-4">
								<label for="formLimit">Max. Allowed Category</label>
								<input type="number" name="categoryLimit" id="categoryLimit" class="form-control" placeholder="Max. Allowed Category" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('leadformspro::app.button.cancelLimit')</button>
					<button type="button" id="saveLimit" class="btn btn-primary">@lang('leadformspro::app.button.saveLimit')</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('scripts')

	<script>
		@if (user()->is_superadmin)
		$('#saveLimit').click(function () {
			var saveButton = $('#saveLimit');
			var url = "{{ route('leadformspro-settings.store') }}";
			// Get form data
			var formData = $('#setLimit').serialize();
			// Button state manipulation for loading
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
					// Restore button state after processing
					saveButton.prop('disabled', false); // Enable button
					saveButton.html('Save Limit'); // Revert button text
				},
				error: function (error) {
					if (error.status === 422) {
						// If it's a validation error
						var errorResponse = JSON.parse(error.responseText);
						Swal.fire({
							icon: 'warning',
							title: 'Validation Error!',
							text: errorResponse.message.formLimit[0],
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
					// Restore button state after processing
					saveButton.prop('disabled', false); // Enable button
					saveButton.html('Save Limit'); // Revert button text
				}
			});
		});

		$('.editLimit').on('click', function () {
			var id = $(this).data('id');
			var url = '{{ route("leadformspro-settings.edit", ['leadformspro_setting' => 'placeholder']) }}'.replace('placeholder', id);

			// AJAX request
			$.ajax({
				url: url,
				type: 'get',
				success: function (response) {

					// Populate the modal fields
					$('#package option[value="' + response.package_id + '"]').prop('selected', true);
					$('#formLimit').val(response.form_limit);
					$('#categoryLimit').val(response.cat_limit);

					// Show the modal
					$('#addLimitModal').modal('show');
				},
				error: function (request, status, error) {
					console.log('AJAX error:', error);
				}
			});
		});

		$('.deleteLimit').on('click', function () {
			var deleteButton = $('.deleteLimit');
			var id = $(this).data('id');
			var token = $('meta[name="csrf-token"]').attr('content');
			var url = '{{ route("leadformspro-settings.destroy", ['leadformspro_setting' => 'placeholder']) }}'.replace('placeholder', id);

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
