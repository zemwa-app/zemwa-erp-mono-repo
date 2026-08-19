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
								<div class="col-sm-8 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('publicassessmentpro::app.header.pageTitle')</p>
								</div>
								<div class="col-sm-4">
									<button type="button" class="btn btn-block btn-primary btn-sm" id="addLimit" data-toggle="modal" data-target="#addLimitModal"><i class="fa fa-plus"></i> @lang('publicassessmentpro::app.button.addLimit')</button>
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
													<th scope="col" class="col-sm-3">Assessment Limit</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
											</thead>
											<tbody>
												@foreach($papPAPSs as $index => $papPAPS)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $papPAPS->getPackage->name }}</td>
														<td>{{ $papPAPS->assessment_limit }}</td>
															<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $papPAPS->id }}" class="btn btn-outline-success btn-sm mx-2 editLimit"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $papPAPS->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteLimit"><i class="fa fa-trash"></i></button>
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
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addLimitModalLabel">@lang('publicassessmentpro::app.header.addLimit')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="setLimit">
						@csrf
					<div class="form-row">
						<div class="col-6">
							<label for="package">Select Package</label>
							<select class="form-control" name="package" id="package" required>
								<option disabled>Select Package</option>
								@foreach($papPackages as $papPackage)
									<option value="{{ $papPackage->id }}">{{ $papPackage->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-6">
							<label for="formLimit">Max. Allowed Assessment(s)</label>
							<input type="number" name="formLimit" id="formLimit" class="form-control" placeholder="Max. Allowed Assessment(s)" required>
						</div>

					</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('publicassessmentpro::app.button.cancelLimit')</button>
					<button type="button" id="saveLimit" class="btn btn-primary">@lang('publicassessmentpro::app.button.saveLimit')</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('scripts')

	<script>
		@if (user()->is_superadmin)
			$('#saveLimit').click(function () {
				var url = "{{ route('publicassessmentpro-settings.store') }}";
				// Get form data
				var formData = $('#setLimit').serialize();

				$.ajax({
					type: 'POST',
					url: url,
					data: formData,
					success: function(response) {
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
					error: function(error) {
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
					}
				});
			});

			$('.editLimit').on('click', function(){
				var id = $(this).data('id');
				var url = '{{ route("publicassessmentpro-settings.edit", ['publicassessmentpro_setting' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function(response){
						//todo: when edit make package not changable
						// Populate the modal fields
						//$('#package').prop("readonly", true); 
						$('#package option[value="' + response.package_id + '"]').prop('selected', true);
						$('#formLimit').val(response.assessment_limit);
					
						// Show the modal
						$('#addLimitModal').modal('show');
					},
					error: function(request, status, error){
						console.log('AJAX error:', error);
					}
				});
			});

			$('.deleteLimit').on('click', function(){
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("publicassessmentpro-settings.destroy", ['publicassessmentpro_setting' => 'placeholder']) }}'.replace('placeholder', id);

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
							success: function(response){
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
							error: function(xhr){
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

		@endif

	</script>
@endpush
