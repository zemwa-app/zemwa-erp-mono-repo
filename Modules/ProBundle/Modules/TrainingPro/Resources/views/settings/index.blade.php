@extends('layouts.app')

@section('content')
<?php
//dd($docCategories);
?>
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
							<a class="nav-link" id="docQuota-tab" data-toggle="tab" href="#docQuota" role="tab" aria-controls="docQuota" aria-selected="false">Document Configurations</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="docCatSubCat-tab" data-toggle="tab" href="#docCatSubCat" role="tab" aria-controls="docCatSubCat" aria-selected="false">Default Categories/Sub categories</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="docTemplate-tab" data-toggle="tab" href="#docTemplate" role="tab" aria-controls="docTemplate" aria-selected="false">Document Templates</a>
						</li>
					</ul>
					<div class="tab-content" id="myTabContent">

						<!-- DOCUMENTS CONFIG WRAPPER -->
						<div class="tab-pane fade show active" id="docQuota" role="tabpanel" aria-labelledby="docQuota-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-9 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-cog"></i> @lang('documentspro::app.header.pageTitle')</p>
									</div>
									<div class="col-sm-3">
										<button type="button" class="btn btn-block btn-primary btn-sm" id="addLimit" data-toggle="modal" data-target="#addLimitModal"><i class="fa fa-plus"></i> @lang('documentspro::app.button.addLimit')</button>
									</div>
								</div>
								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($docConfigurations) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-6">Package Name</th>
													<th scope="col" class="col-sm-2">Template Limit</th>
													<th scope="col" class="col-sm-2">Document Limit</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($docConfigurations as $index => $item)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $item->getPackage->name }}</td>
														<td>{{ $item->templates_limit }}</td>
														<td>{{ $item->documents_limit }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $item->id }}" class="btn btn-outline-success btn-sm mx-2 editLimit"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $item->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteLimit"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										@else
											<div class="alert alert-warning" role="alert">
												<i class="fa fa-info mx-2"></i> @lang('documentspro::app.message.noDataFound')
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>

						<!-- DEFAULT CATEGORIES/SUB-CATEGORIES WRAPPER -->
						<div class="tab-pane fade" id="docCatSubCat" role="tabpanel" aria-labelledby="docCatSubCat-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-12 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('documentspro::app.header.defaultCatSubCatTitle')</p>
									</div>
								</div>

								<div class="row my-2 p-3">
									<!-- DEFAULT CATEGORIES WRAPPER -->
									<div class="col-6 table-responsive">
										<div class="row">
											<div class="col-6 px-0 mb-3"></div>
											<div class="col-6 mb-3">
												<button type="button" class="btn btn-block btn-primary btn-sm" id="addCategory" data-toggle="modal" data-target="#addCategoryModal"><i class="fa fa-plus"></i> @lang('documentspro::app.button.addCategory')</button>
											</div>
										</div>
										@if(count($docCategories) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-3">Category Name</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($docCategories as $index => $docCategory)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $docCategory->name }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $docCategory->id }}" class="btn btn-outline-success btn-sm mx-2 editCategory"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $docCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteCategory"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										@else
											<div class="alert alert-warning" role="alert">
												<i class="fa fa-info mx-2"></i> @lang('documentspro::app.message.noDataFound')
											</div>
										@endif
									</div>
									<!-- DEFAULT SUB-CATEGORIES WRAPPER -->
									<div class="col-6 table-responsive">

										<div class="row">
											@if(count($docCategories) != 0)
												<div class="col-6 px-0 mb-3"></div>
												<div class="col-6 mb-3">
													<button type="button" class="btn btn-block btn-primary btn-sm" id="addSubCategory" data-toggle="modal" data-target="#addSubCategoryModal"><i class="fa fa-plus"></i> @lang('documentspro::app.button.addSubCategory')</button>
												</div>
											@else
												<div class="col-12 mb-3 bg-light">
													<p class="d-flex align-items-center justify-content-center h-100 text-info">First, add a category to create a sub-category.</p>
												</div>
											@endif
										</div>

										@if(count($docSubCategories) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-3">Sub Category Name</th>
													<th scope="col" class="col-sm-3">Associated Category</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($docSubCategories as $index => $docSubCategory)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $docSubCategory->name }}</td>
														<td>{{ $docSubCategory->getCategory->name }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $docSubCategory->id }}" class="btn btn-outline-success btn-sm mx-2 editSubCategory"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $docSubCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteSubCategory"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										@else
											<div class="alert alert-warning" role="alert">
												<i class="fa fa-info mx-2"></i> @lang('documentspro::app.message.noDataFound')
											</div>
										@endif
									</div>
								</div>
							</div>
						</div>

						<!-- TEMPLATE WRAPPER -->
						<div class="tab-pane fade" id="docTemplate" role="tabpanel" aria-labelledby="docTemplate-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-10 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('documentspro::app.header.templateTitle')</p>
									</div>
									<div class="col-sm-2">
										<button type="button" class="btn btn-block btn-primary btn-sm" id="addTemplate" data-toggle="modal" data-target="#addTemplateModal"><i class="fa fa-plus"></i> @lang('documentspro::app.button.addTemlpate')</button>
									</div>
								</div>

								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($docTemplates) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-2">Templates Name</th>
													<th scope="col" class="col-sm-2">Thumbnails</th>
													<th scope="col" class="col-sm-2">Sub Category</th>
													<th scope="col" class="col-sm-2">Category</th>
													<th scope="col" class="col-sm-1">Status</th>
													<th scope="col" class="col-sm-2"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($docTemplates as $index => $docTemplate)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $docTemplate->name }}</td>
														<td>
															@if(isset($docTemplate->thumbnail))
																<div class="image-container">
																	<img src="{{ asset(Storage::disk('public')->url($docTemplate->thumbnail)) }}" alt="{{ $docTemplate->name }}" class="img-thumbnail" onclick="showImageModal('{{ asset(Storage::disk('public')->url($docTemplate->thumbnail)) }}')" style="width: auto; height: 50px; cursor: pointer;">
																</div>
																<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
																	<div class="modal-dialog modal-dialog-centered" role="document">
																		<div class="modal-content">
																			<div class="modal-body d-flex align-items-center justify-content-center">
																				<img src="" alt="Uploaded Image" id="modalImage" class="img-fluid">
																			</div>
																		</div>
																	</div>
																</div>
															@else
																<div class="image-container">
																	<img src="{{ asset(Storage::disk('public')->url($docTemplate->thumbnail)) }}" alt="NA" class="img-thumbnail" style="width: auto; height: 50px;">
																</div>
															@endif
														</td>
														<td>{{ $docTemplate->subCategory->name }}</td>
														<td>{{ $docTemplate->subCategory->getCategory->name }}</td>
														<td>{{ $docTemplate->is_draft == 0 ? 'Draft' : ($docTemplate->is_draft == 1 ? 'Published' : 'Unknown') }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $docTemplate->id }}" class="btn btn-outline-success btn-sm mx-2 editDocTemplate"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $docTemplate->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteDocTemplate"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
												@endforeach
												</tbody>
											</table>
										@else
											<div class="alert alert-warning" role="alert">
												<i class="fa fa-info mx-2"></i> @lang('documentspro::app.message.noDataFound')
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

	<!-- ADD DOCUMENT LIMIT MODAL -->
	<div class="modal fade" id="addLimitModal" data-backdrop="static" tabindex="-1" aria-labelledby="addLimitModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addLimitModalLabel">@lang('documentspro::app.header.addLimit')</h5>
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
										<option value="{{ $package->id }}">{{ $package->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-4">
								<label for="formLimit">Max. Allowed Templates</label>
								<input type="number" name="tempLimit" id="tempLimit" class="form-control" placeholder="Max. Allowed Templates" required>
							</div>
							<div class="col-4">
								<label for="formLimit">Max. Allowed Documents</label>
								<input type="number" name="docLimit" id="docLimit" class="form-control" placeholder="Max. Allowed Documents" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('documentspro::app.button.cancelLimit')</button>
					<button type="button" id="saveLimit" class="btn btn-primary">@lang('documentspro::app.button.saveLimit')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ADD CATEGORY MODAL -->
	<div class="modal fade" id="addCategoryModal" data-backdrop="static" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addCategoryModalLabel">@lang('documentspro::app.header.addCategory')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="addCategoryForm">
						@csrf
						<div class="form-row">
							<div class="col-12">
								<label for="docCategory"></label>
								<input type="text" name="docCategory" id="docCategory" class="form-control" placeholder="Create/Edit category" required>
								<input type="hidden" name="docCategoryId" id="docCategoryId" value="">
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('documentspro::app.button.cancelLimit')</button>
					<button type="button" id="btnSaveCategory" class="btn btn-primary">@lang('documentspro::app.button.saveCategory')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ADD SUB CATEGORY MODAL -->
	<div class="modal fade" id="addSubCategoryModal" data-backdrop="static" tabindex="-1" aria-labelledby="addSubCategoryModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addSubCategoryModalLabel">@lang('documentspro::app.header.addSubCategory')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="addSubCategoryForm">
						@csrf
						<div class="form-row">
							<div class="col-4">
								<label for="frmDocCategory">Select Category</label>
								<select class="form-control" name="frmDocCategory" id="frmDocCategory" required>
									<option selected>Select Category</option>
									@if(count($docCategories) != 0)
										@foreach($docCategories as $docCategory)
											<option value="{{ $docCategory->id }}">{{ $docCategory->name }}</option>
										@endforeach
									@endif
								</select>
							</div>
							<div class="col-8">
								<label for="formLimit">Sub-Category Name</label>
								<input type="text" name="frmSubCatName" id="frmSubCatName" class="form-control" placeholder="Enter Sub-Category Name" required>
								<input type="hidden" name="frmSubCatId" id="frmSubCatId" value="">
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('documentspro::app.button.cancelLimit')</button>
					<button type="button" id="btnSaveSubCategory" class="btn btn-primary">@lang('documentspro::app.button.saveSubCategory')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- ADD DOCUMENT TEMPLATE MODAL -->
	<div class="modal fade" id="addTemplateModal" data-backdrop="static" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addTemplateLabel">@lang('documentspro::app.header.addTemplate')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="docTempForm" enctype="multipart/form-data">
						@csrf
						<input type="hidden" name="id" id="tempId" value ="">

						<div class="form-row">
							<div class="form-group col-md-4">
								<label for="frmDocTempName">Template Name:</label>
								<input type="text" class="form-control" id="frmDocTempName" name="frmDocTempName" required>
							</div>
							<div class="form-group  col-md-4">
								<label for="frmDocTempCat">Select Category</label>
								<select class="form-control" name="frmDocTempCat" id="frmDocTempCat" required>
									<option selected>Select Category</option>
									@if(count($docCategories) != 0)
										@foreach($docCategories as $docCategory)
											<option value="{{ $docCategory->id }}">{{ $docCategory->name }}</option>
										@endforeach
									@endif
								</select>
							</div>
							<div class="form-group  col-md-4">
								<label for="frmDocTempSubCat">Select Sub-category</label>
								<select class="form-control" name="frmDocTempSubCat" id="frmDocTempSubCat" required>
									<option selected>Select Sub-category</option>
								</select>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group col-md-5">
								<label for="frmDocTempFile">Template:</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" id="frmDocTempFile" name="frmDocTempFile" accept=".zw">
									<label class="custom-file-label" for="frmDocTempFile">Choose file (Max 1MB)</label>
								</div>
							</div>
							<div class="form-group col-md-4">
								<label for="frmDocTempThumb">Template Image:</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" id="frmDocTempThumb" name="frmDocTempThumb" accept="image/*">
									<label class="custom-file-label" for="frmDocTempThumb">Choose file (Max 1MB)</label>
								</div>
							</div>
							<div class="form-group col-md-3 d-flex align-items-end justify-content-center">
								<div class="custom-control custom-switch d-flex align-items-center justify-content-center">
									<span class="mr-2">Draft</span>
									<div class="custom-control custom-switch">
										<input type="checkbox" name="frmDocTempStat" class="custom-control-input" id="frmDocTempStat">
										<label class="custom-control-label" for="frmDocTempStat">Publish</label>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('documentspro::app.button.cancelLimit')</button>
					<button type="button" id="saveDocTemplate" class="btn btn-primary">@lang('documentspro::app.button.saveTemlpate')</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('scripts')
	<script>
		@if (user()->is_superadmin)
			$('#saveLimit').click(function () {
				var url = "{{ route('documentspro-settings.store') }}";
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
							var errorResponse = error.responseJSON;
							var errorMessage = '';

							// Construct a message from the validation errors
							$.each(errorResponse.message, function(key, value) {
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

			$('.editLimit').on('click', function(){
				var id = $(this).data('id');
				var url = '{{ route("documentspro-settings.edit", ['documentspro_setting' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function(response){

						// Populate the modal fields
						$('#package option[value="' + response.package_id + '"]').prop('selected', true);
						$('#tempLimit').val(response.temp_limit);
						$('#docLimit').val(response.doc_limit);

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
				var url = '{{ route("documentspro-settings.destroy", ['documentspro_setting' => 'placeholder']) }}'.replace('placeholder', id);

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

{{--			document.getElementById('templateImage').addEventListener('change', function (e) {--}}
{{--				var fileName = e.target.files[0].name;--}}
{{--				var nextSibling = e.target.nextElementSibling;--}}
{{--				nextSibling.innerText = fileName;--}}
{{--			});--}}

			$('#btnSaveCategory').click(function () {

				var url = "{{ route('docCategory.store') }}";
				// Get form data
				var formData = $('#addCategoryForm').serialize();

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
								$('#addCategoryModal').modal('hide');
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
							var errorResponse = error.responseJSON;
							var errorMessage = '';

							// Construct a message from the validation errors
							$.each(errorResponse.message, function(key, value) {
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

			$('.editCategory').on('click', function(){
				var id = $(this).data('id');
				var url = '{{ route("docCategory.edit", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function(response){

						// Populate the modal fields
						$('#docCategoryId').val(response.cat_id);
						$('#docCategory').val(response.cat_name);

						// Show the modal
						$('#addCategoryModal').modal('show');
					},
					error: function(request, status, error){
						console.log('AJAX error:', error);
					}
				});
			});

			$('.deleteCategory').on('click', function(){
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("docCategory.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			$('#btnSaveSubCategory').click(function () {

				var url = "{{ route('docSubCategory.store') }}";
				// Get form data
				var formData = $('#addSubCategoryForm').serialize();

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
								$('#addSubCategoryModal').modal('hide');
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
							var errorResponse = error.responseJSON;
							var errorMessage = '';

							// Construct a message from the validation errors
							$.each(errorResponse.message, function(key, value) {
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

			$('.editSubCategory').on('click', function(){
				var id = $(this).data('id');
				var url = '{{ route("docSubCategory.edit", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function(response){

						// Populate the modal fields
						$('#frmSubCatId').val(response.sub_cat_id);
						$("#frmDocCategory").val(response.cat_id);
						$('#frmSubCatName').val(response.sub_cat_name);

						// Show the modal
						$('#addSubCategoryModal').modal('show');
					},
					error: function(request, status, error){
						console.log('AJAX error:', error);
					}
				});
			});

			$('.deleteSubCategory').on('click', function(){
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("docSubCategory.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			// Function to fetch and populate sub-categories
			function populateSubCategories(categoryId) {
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("getSubCategory", ['id' => 'placeholder']) }}'.replace('placeholder', categoryId);

				$.ajax({
					type: 'GET',
					url: url,
					headers: {
						'X-CSRF-TOKEN': token
					},
					success: function (data) {
						// Clear existing options
						$('#frmDocTempSubCat').empty();

						// Check if the response has sub-category data
						if ('sub_cat' in data && Array.isArray(data.sub_cat) && data.sub_cat.length > 0) {
							// Add a default option
							$('#frmDocTempSubCat').append('<option selected>Select Sub-category</option>');

							// Append the new options based on the response
							$.each(data.sub_cat, function (index, subCategory) {
								$('#frmDocTempSubCat').append('<option value="' + subCategory.id + '">' + subCategory.name + '</option>');
							});
						} else {
							// Add a default option
							$('#frmDocTempSubCat').append('<option selected>No Sub-category Found</option>');
							console.error('Invalid or empty response format');
						}
					},
					error: function (xhr, status, error) {
						console.error('Error fetching sub-categories:', error);
					}
				});
			}

			$('#saveDocTemplate').click(function () {

				var url = "{{ route('docTemp.store') }}";
				var csrfToken = $('meta[name="csrf-token"]').attr('content');

				// Get form data
				var formData = new FormData();
				var checkboxValue = $('#frmDocTempStat').is(':checked') ? 1 : 0;
				formData.append('id', $('#tempId').val());
				formData.append('frmDocTempName', $('#frmDocTempName').val());
				formData.append('frmDocTempCat', $('#frmDocTempCat').val());
				formData.append('frmDocTempSubCat', $('#frmDocTempSubCat').val());
				formData.append('frmDocTempFile', $('#frmDocTempFile')[0].files[0]);
				formData.append('frmDocTempThumb', $('#frmDocTempThumb')[0].files[0]);
				formData.append('frmDocTempStat', checkboxValue);

				$.ajax({
					type: 'POST',
					url: url,
					data: formData,
					contentType: false,
					processData: false,
					headers: {
						'X-CSRF-TOKEN': csrfToken
					},
					success: function(response) {
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
					error: function(error) {
						if (error.status === 422) {
							// If it's a validation error
							var errorResponse = error.responseJSON;
							var errorMessage = '';

							// Construct a message from the validation errors
							$.each(errorResponse.message, function(key, value) {
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

			$('.editDocTemplate').on('click', function(){
				var id = $(this).data('id');
				var url = '{{ route("docTemp.edit", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function(response){
						var checkboxValue = parseInt(response.frmDocTempStat);
						// Populate the modal fields
						$('#tempId').val(response.id);
						$('#frmDocTempName').val(response.frmDocTempName);
						$('#frmDocTempCat').val(response.frmDocTempCat);
						$('#frmDocTempStat').prop('checked', checkboxValue === 1);

						// Clear existing options in frmDocTempSubCat
						$('#frmDocTempSubCat').empty();

						// Check if subCat is an array or iterable object
						if (Array.isArray(response.subCat) && response.subCat.length > 0) {
							// Iterate over subCat using $.each
							$.each(response.subCat, function (index, subcategory) {
								$('#frmDocTempSubCat').append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
							});
						}

						// Set the selected option for frmDocTempSubCat
						$('#frmDocTempSubCat').val(response.frmDocTempSubCat);

						// Show the modal
						$('#addTemplateModal').modal('show');
					},
					error: function(request, status, error){
						console.log('AJAX error:', error);
					}
				});
			});

			$('.deleteDocTemplate').on('click', function(){
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("docTemp.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			// Event listener for category selection change
			$('#frmDocTempCat').on('change', function () {
				var selectedCategoryId = $(this).val();

				// Call the function to populate sub-categories
				populateSubCategories(selectedCategoryId);
			});

			function showImageModal(imagePath) {
				$('#modalImage').attr('src', imagePath);
				$('#imageModal').modal('show');
			}

			// Function to enable/disable save button based on form values
			function toggleSaveButton() {
				var selectedValue = document.getElementById('frmDocCategory').value;
				var subCatName = document.getElementById('frmSubCatName').value;
				var saveButton = document.getElementById('btnSaveSubCategory');

				// Check if the form elements have values
				var isSaveDisabled = (selectedValue === 'Select Category' || subCatName.trim() === '');

				saveButton.disabled = isSaveDisabled;
			}

			// Function to reset the form
			function resetForm() {
				document.getElementById('frmDocCategory').value = 'Select Category';
				document.getElementById('frmSubCatName').value = '';
				document.getElementById('frmSubCatId').value = '';
				toggleSaveButton(); // Disable the save button after reset
			}

			// Attach event listener to the select dropdown and input field
			document.getElementById('frmDocCategory').addEventListener('change', toggleSaveButton);
			document.getElementById('frmSubCatName').addEventListener('input', toggleSaveButton);

			// Initial state setup
			toggleSaveButton();

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
		@endif
	</script>
@endpush
