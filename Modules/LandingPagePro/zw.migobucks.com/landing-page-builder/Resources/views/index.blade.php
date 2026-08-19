@extends('layouts.app')

@push('datatable-styles')

@endpush

@php
	$viewStatus = user()->permission('view_landingpage');
	$addStatus = user()->permission('add_landingpage');
	$updateStatus = user()->permission('edit_landingpage');
	$deleteStatus = user()->permission('delete_landingpage');
	$companyId = company()->id;
	//dd($viewStatus);
@endphp

@section('content')
	<style>
		.card {
			height: 200px;
			overflow: hidden;
		}

		.card img {
			object-fit: cover;
		}

		/* Full-screen Modal Style */
		#lpBuilderModal .modal-full {
			min-width: 100%;
			margin: 0;
		}

		#lpBuilderModal .modal-content {
			min-height: 100vh;
		}
	</style>
	<!-- CONTENT WRAPPER START -->
	<div class="content-wrapper">
		<!-- Add Task Export Buttons Start -->
		<div class="d-grid d-lg-flex d-md-flex action-bar">
			<div id="table-actions" class="flex-grow-1 align-items-center">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="lplist-tab" data-toggle="tab" href="#lplist" role="tab" aria-controls="lplist" aria-selected="true">Landing Pages</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="lpbuilder-tab" data-toggle="tab" href="#lpbuilder" role="tab" aria-controls="lpbuilder" aria-selected="false">Landing Page Builder</a>
					</li>
					@if( user()->permission('add_landingpagecategory') !== 'none' )
						<li class="nav-item">
							<a class="nav-link" id="category-tab" data-toggle="tab" href="#category" role="tab" aria-controls="category" aria-selected="false">Categories</a>
						</li>
					@endif
				</ul>
				<div class="tab-content" id="myTabContent">

					<div class="tab-pane fade show active" id="lplist" role="tabpanel" aria-labelledby="lplist-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-12 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('landingpagepro::app.header.landingPageTitle')</p>
								</div>
								<div class="col mt-3">
									<div class="alert alert-info alert-dismissible fade show" role="alert">
										You've created <strong>{{ $userPageCount }} lead forms</strong> and you can create <strong>{{ $userPageLimit - $userPageCount }}</strong> more lead forms. Your maximum allowed limit is <strong>{{ $userPageLimit }} lead forms</strong>.
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
								</div>
							</div>
							<div class="row my-2 p-3">
								<div class="col table-responsive">
									@if(count($landingPages) != 0)
										<table class="table">
											<thead class="">
											<tr>
												<th scope="col" class="col-sm-1 align-middle">#</th>
												<th scope="col" class="col-sm-2 align-middle">Landing Page Name</th>
												<th scope="col" class="col-sm-1 align-middle">Template</th>
												<th scope="col" class="col-sm-2 align-middle">Category</th>
												<th scope="col" class="col-sm-2 align-middle">Sharable Links<br><small>Click on the link to copy the url</small></th>
												<th scope="col" class="col-sm-2 align-middle">Status</th>
												<th scope="col" class="col-sm-2 align-middle">Action</th>
											</tr>
											</thead>
											<tbody>
											@foreach($landingPages as $index => $landingPage)
												<tr>
													<td>{{ $index + 1 }}</td>
													<td>{{ $landingPage->name ?: 'Not defined' }}</td>
													<td>{{ $landingPage->templateName ?: 'Not defined' }}</td>
													<td>{{ $landingPage->categoryName ?: 'Not defined' }}</td>
													<td class="px-2">
														@if($landingPage->statusText === "Active")
															<button type="button" data-toggle="tooltip" data-placement="bottom" title="Copy web URL." data-url="{{ route('template.page', ['id' => Crypt::encrypt($landingPage->id)]) }}" class="btn btn-outline-info btn-sm mx-2 copy-button"><i class="fa fa-link"></i></button>
															<button type="button" data-toggle="tooltip" data-placement="bottom" title="Copy iFrame code." data-url="&lt;iframe src='{{ route('template.page', ['id' => Crypt::encrypt($landingPage->id)]) }}' frameborder='0' scrolling='yes' style='display:block; width:100%; height:60vh;'>&lt;/iframe&gt;" class="btn btn-outline-info btn-sm mx-2 copy-button"><i class="fa fa-globe"></i></button>
														@else
															<strong>-</strong>
														@endif
													</td>
													<td>{{ $landingPage->statusText ?: 'Not defined' }}</td>
													<td class="px-2">
														<button type="button" data-id="{{ \Crypt::encrypt($landingPage->id) }}" class="btn btn-outline-success btn-sm mx-2 editLandingPage"><i class="fa fa-edit"></i></button>
														<button type="button" data-id="{{ \Crypt::encrypt($landingPage->id) }}" class="btn btn-outline-danger btn-sm mx-2 deleteLandingPage"><i class="fa fa-trash"></i></button>
													</td>
												</tr>
											@endforeach
											<tr>
												<td colspan="7"><strong class="text-warning"><small><i class="fa fa-info-circle fa-1x"></i> Once the status of template is "Active" you will be able to see the 'Copy URL' option in Sharable Links column.</small></strong></td>
											</tr>
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

					<div class="tab-pane fade" id="lpbuilder" role="tabpanel" aria-labelledby="lpbuilder-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-12 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('landingpagepro::app.header.lpBuilderTitle')</p>
								</div>
								<div class="col-12 mt-2 p-2 bg-light">
									<i class="fa fa-info-circle mx-2"></i> To preview the template, click on the image. To create a landing page, select the template by clicking it's name and click the <strong>"Create Landing Page"</strong>.
								</div>
							</div>

							<div class="row my-2 p-3">
								<div class="col table-responsive">
									<div class="container-fluid">
										<div class="row">
											@if(count($templatePages) != 0)
												<div class="col-12 mt-3">
													<div class="col-12 mt-3">
														<div class="card-deck">
															@foreach($templatePages as $templatePage)
																<div class="card" data-id="{{ $templatePage->id }}" style="cursor: pointer;">
																	<div class="card-body">
																		<img src="{{ asset(Storage::disk('public')->url($templatePage->thumbnail)) }}" class="card-img-tops" alt="Thumbnail" onclick="showImageModal('{{ asset(Storage::disk('public')->url($templatePage->thumbnail)) }}')">
																	</div>
																	<div class="card-footer bg-white d-flex align-items-center justify-content-center" onclick="selectRadioButton('{{ $templatePage->id }}')">
																		<input type="radio" name="thumbnailRadio" id="radio{{ $templatePage->id }}" value="{{ $templatePage->id }}">&nbsp;
																		<label for="radio{{ $templatePage->id }}" class="card-title font-weight-bold mb-0">{{ $templatePage->name }}</label>
																	</div>
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
															@endforeach
														</div>
													</div>
												</div>
												<div class="col-12 mt-3 text-right">
													<button type="button" class="btn btn-outline-success btn-sm mx-2 lpBuilder">
														<i class="fa fa-plus"></i> @lang('landingpagepro::app.button.addPage')
													</button>
												</div>
											@else
												<div class="col-12 alert alert-warning" role="alert"><i class="fa fa-info mx-2"></i> @lang('landingpagepro::app.message.noDataFound')</div>
											@endif
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					@if( user()->permission('add_landingpagecategory') !== 'none')
						<div class="tab-pane fade" id="category" role="tabpanel" aria-labelledby="category-tab">
							<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
								<div class="row my-2">
									<div class="col-sm-10 d-flex align-items-center">
										<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('landingpagepro::app.header.categoryTitle')</p>
									</div>
									<div class="col-sm-2">
										<button type="button" class="btn btn-block btn-primary btn-sm" id="addLeadCategory" data-toggle="modal" data-target="#addCategoryFormModal"><i class="fa fa-plus"></i> @lang('landingpagepro::app.button.addCategory')</button>
									</div>
								</div>

								<div class="row my-2 p-3">
									<div class="col table-responsive">
										@if(count($lpCategories) != 0)
											<table class="table">
												<thead class="">
												<tr>
													<th scope="col" class="col-sm-1">#</th>
													<th scope="col" class="col-sm-6">Category Name</th>
													<th scope="col" class="col-sm-3"></th>
												</tr>
												</thead>
												<tbody>
												@foreach($lpCategories as $index => $lpCategory)
													<tr>
														<td>{{ $index + 1 }}</td>
														<td>{{ $lpCategory->name }}</td>
														<td class="d-flex justify-content-end px-2">
															<button type="button" data-id="{{ $lpCategory->id }}" class="btn btn-outline-success btn-sm mx-2 editCategory"><i class="fa fa-edit"></i></button>
															<button type="button" data-id="{{ $lpCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteCategory"><i class="fa fa-trash"></i></button>
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
					@endcanany
				</div>
			</div>
		</div>
	</div>
	<!-- CONTENT WRAPPER END -->

	<!-- ADD LANDING PAGE CATEGORY -->
	<div class="modal fade" id="addCategoryFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addCategoryFormModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addCategoryModalLabel">@lang('landingpagepro::app.header.addCategory')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="createCategory">
						@csrf
						<div class="form-row">
							<div class="col-12">
								<input type="hidden" name="lpCatId" id="lpCatId" class="form-control form-control-lg" value="">
								<input type="text" name="lpCatName" id="lpCatName" class="form-control form-control-lg" placeholder="Enter Category Name" value="" minlength="3" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('landingpagepro::app.button.cancelLimit')</button>
					<button type="button" id="saveCategory" class="btn btn-primary">@lang('landingpagepro::app.button.saveCategory')</button>
				</div>
			</div>
		</div>
	</div>

	<!-- LANDING PAGE BUILDER -->
	<div class="modal fade" id="lpBuilderModal" tabindex="-1" role="dialog" aria-labelledby="lpBuilderLabel" aria-hidden="true">
		<div class="modal-dialog modal-full" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="lpBuilderLabel">Landing Page Editor</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body p-0" id="lpBuilderContent">
					<div class="row h-100">
						<div class="col-md-3 h-100 overflow-auto" id="templateForm" style="max-height: calc(100vh - 150px); border: none;"></div>

						<div class="col-md-9 h-100 p-0" id="iframeContainer">
							<iframe id="templatePreview" src="" style="min-width: 100%; min-height: calc(100vh - 150px); border: none;"></iframe>
						</div>
					</div>
				</div>
				<div class="modal-footer">

					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					<button type="button" id="preview" class="btn btn-info">Save & Preview</button>
{{--					<button type="button" class="btn btn-success">Save and Close</button>--}}
					<!-- Other buttons can be added here -->
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		$(document).ready(function () {
			var modal = $('#lpBuilderModal');
			modal.on('hidden.bs.modal', function () {
				location.reload();
			});

			$('input[name="thumbnailRadio"]').change(function () {
				if ($('input[name="thumbnailRadio"]:checked').length > 0) {
					$('.lpBuilder').prop('disabled', false);
				} else {
					$('.lpBuilder').prop('disabled', true);
				}
			});

			$('#saveCategory').click(function () {
				var url = "{{ route('lpCategory.store') }}";
				// Get form data
				var formData = $('#createCategory').serialize();

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
								$('#addCategoryFormModal').modal('hide');
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
						if (error.status === 422 || error.status === 403) {
							// If it's a validation error
							var errorResponse = JSON.parse(error.responseText);
							Swal.fire({
								icon: 'warning',
								title: 'Error!',
								text: errorResponse.message.leadCatName[0],
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

			$('.editCategory').on('click', function () {
				var id = $(this).data('id');
				var url = '{{ route("lpCategory.edit", ["id" => "placeholder"]) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {
						// Populate the modal fields
						$('#lpCatId').val(response.id);
						$('#lpCatName').val(response.name);
						// Show the modal
						$('#addCategoryFormModal').modal('show');
					},
					error: function (request, status, error) {
						console.log('AJAX error:', error);
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: error,
							confirmButtonText: 'OK'
						});
					}
				});
			});

			$('.deleteCategory').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("lpCategory.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			$('.lpBuilder').on('click', function () {
				// Get the value of the selected radio button
				var id = $('input[name="thumbnailRadio"]:checked').val();
				var url = '{{ route("template.editor", ["id" => "placeholder"]) }}'.replace('placeholder', id);
				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {
						$('#templateForm').html('');
						$('#templateForm').load(response.formView);
						$('#templatePreview').attr('src', response.iframeView);
						// Show the modal
						$('#lpBuilderModal').modal('show');
					},
					error: function (request, status, error) {
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: error,
							confirmButtonText: 'OK'
						});
					}
				});
			});

			$('#preview').click(function () {
				var url = "{{ route('landingpage.update') }}";
				// Get form data
				var formData = $('#lpTemplate').serialize();

				$.ajax({
					type: 'POST',
					url: url,
					data: formData,
					dataType: 'json',
					success: function (response) {
						$('#templatePreview').attr("src", $('#templatePreview').attr("src"));
					},
					error: function (error) {
						try {
							var errorResponse = JSON.parse(error.responseText);
							var errorMessage = "Validation Error:";

							if (error.status === 422 || error.status === 403) {
								for (var key in errorResponse.error) {
									errorMessage += "\n" + errorResponse.error[key][0];
								}

								Swal.fire({
									icon: 'warning',
									title: 'Validation Error!',
									text: errorMessage,
									confirmButtonText: 'OK'
								});
							} else {
								errorMessage = "Error: " + JSON.stringify(errorResponse.error);
								Swal.fire({
									icon: 'warning',
									title: 'Error!',
									text: errorMessage,
									confirmButtonText: 'OK'
								});
							}
						} catch (e) {
							console.error('Error parsing response:', e);
						}
					}

				});
			});

			$('.editLandingPage').on('click', function () {
				// Get the value of the selected radio button
				var id = $(this).data('id');
				var url = '{{ route("template.edit", ["id" => "placeholder"]) }}'.replace('placeholder', id);
				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {
						$('#templateForm').html('');
						$('#templateForm').load(response.formView);
						$('#templatePreview').attr('src', response.iframeView);
						// Show the modal
						$('#lpBuilderModal').modal('show');
					},
					error: function (request, status, error) {
						Swal.fire({
							icon: 'warning',
							title: 'Error!',
							text: error,
							confirmButtonText: 'OK'
						});
					}
				});
			});

			$('.deleteLandingPage').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("template.delete", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

		function showImageModal(imagePath) {
			$('#modalImage').attr('src', imagePath);
			$('#imageModal').modal('show');
		}

		function selectRadioButton(templateId) {
			$('#radio' + templateId).prop('checked', true);
		}
	</script>
@endpush
