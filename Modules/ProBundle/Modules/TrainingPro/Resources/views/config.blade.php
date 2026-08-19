@extends('layouts.app')

@push('datatable-styles')

@endpush

@php
	$companyId = company()->id;
@endphp

@section('content')
	<div class="container-fluid">
		<div class="col-sm-12 rounded border border-grey bg-white py-4 text-right">
			@if (in_array('trainingpro', user_modules()) && in_array('admin', user_roles()))
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('trainingpro.index') }}"><i class="fa fa-home"></i></a>
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.results') }}"><i class="fa fa-check-square"></i> Assessment Summary</a>
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.home') }}"><i class="fa fa-cog"></i> Configuration</a>
			@endif
			@if (in_array('trainingpro', user_modules()) && !in_array('admin', user_roles()))
				<a class="btn btn-outline-secondary btn-sm" href="{{ route('config.trainings') }}"><i class="fa fa-tasks"></i> My Trainings</a>
				<a class="btn btn-outline-danger btn-sm active" href="{{ route('config.assessments') }}"><i class="fa fa-poll"></i> My Assessments</a>
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
						<a class="nav-link" id="assessment-tab" data-toggle="tab" href="#assessment" role="tab" aria-controls="assessment" aria-selected="true">Assessments</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" id="assignhub-tab" data-toggle="tab" href="#assignhub" role="tab" aria-controls="assignhub" aria-selected="false">AssignHub</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="configurator-tab" data-toggle="tab" href="#configurator" role="tab" aria-controls="configurator" aria-selected="false">Configurator</a>
					</li>
				</ul>
				<div class="tab-content" id="myTabContent">

					<div class="tab-pane fade show active" id="assessment" role="tabpanel" aria-labelledby="assessment-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-10 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('trainingpro::app.header.assessmentTitle')</p>
								</div>
								<div class="col-sm-2">
									<button type="button" class="btn btn-outline-danger btn-sm" id="addAssessment" data-toggle="modal" data-target="#addAssessmentFormModal"><i class="fa fa-plus"></i> @lang('trainingpro::app.button.addAssessment')</button>
								</div>
							</div>

							<div class="row my-2 p-3">
								<div class="col table-responsive">
									@if(count($tpAssessments) != 0)
										<table class="table">
											<thead class="">
											<tr>
												<th scope="col" class="col-sm-1">#</th>
												<th scope="col" class="col-sm-4">Assessment Name</th>
												<th scope="col" class="col-sm-1 text-center">Min. Score</th>
												<th scope="col" class="col-sm-1 text-center">Max. Score</th>
												<th scope="col" class="col-sm-1 text-center">Duration</th>
												<th scope="col" class="col-sm-1 text-center">Order</th>
												<th scope="col" class="col-sm-1 text-center">Status</th>
												<th scope="col" class="col-sm-2"></th>
											</tr>
											</thead>
											<tbody>
											@foreach($tpAssessments as $index => $tpAssessment)
												<tr>
													<td>{{ $index + 1 }}</td>
													<td>
														<a class="f-14 p-2 mr-3 openRightModal" href="{{ route('config.showQa', ['id' => $tpAssessment->id]) }}">
															<strong>{{ $tpAssessment->name }}</strong>
														</a><br>
														<span class="small text-info">
															({{ $tpAssessment->programmes->name }}/
															{{ $tpAssessment->programmes->category->name }})
														</span>
													</td>
													<td class="text-center">{{ $tpAssessment->min_score }}</td>
													<td class="text-center">{{ $tpAssessment->max_score }}</td>
													<td class="text-center">{{ $tpAssessment->duration == 0 ? '-NA-' : $tpAssessment->duration . ' mins' }}</td>
													<td class="text-center">{{ $tpAssessment->order }}</td>
													<td class="text-center"><i class="fas fa-circle {{ $tpAssessment->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
													<td class="d-flex justify-content-end px-2">
														<div class="dropdown">
															<button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="top" title="Click to manage Q&A">
																<i class="fa fa-question-circle mr-1"></i>
															</button>
															<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
																<a class="dropdown-item btn btn-outline-success btn-sm f-14 p-2 mr-3 openRightModal" href="{{ route('config.showQa', ['id' => $tpAssessment->id]) }}"><i class="fa fa-eye mr-1"></i> View Q&A</a>
																<a class="dropdown-item btn btn-outline-success btn-sm f-14 p-2 mr-3 openRightModal" href="{{ route('config.createQa', ['id' => $tpAssessment->id]) }}"><i class="fa fa-plus mr-1"></i> Add Q&A</a>
															</div>
														</div>
														<button type="button" data-id="{{ $tpAssessment->id }}" class="btn btn-outline-success btn-sm mx-2 editAssessment" data-toggle="tooltip" data-placement="top" title="Edit Assessment"><i class="fa fa-edit"></i></button>
														<button type="button" data-id="{{ $tpAssessment->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteAssessment" data-toggle="tooltip" data-placement="top" title="Delete Assessment"><i class="fa fa-trash"></i></button>
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

					<div class="tab-pane fade" id="assignhub" role="tabpanel" aria-labelledby="assignhub-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-12 d-flex justify-content-between align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('trainingpro::app.header.assignHubTitle')</p>
									<a class="btn btn-outline-danger btn-sm openRightModal" href="{{ route('config.createAssignee') }}">
										<i class="fa fa-plus mr-1"></i> @lang('trainingpro::app.button.addAssignee')
									</a>
								</div>
							</div>
							<div class="row my-2 p-3">
								<div class="col table-responsive">
									@if(count($assignees) != 0)
										<table class="table">
											<thead class="">
											<tr>
												<th scope="col" class="col-sm-1">#</th>
												<th scope="col" class="col-sm-1">Category</th>
												<th scope="col" class="col-sm-2">Programme</th>
												<th scope="col" class="col-sm-2">Designation</th>
												<th scope="col" class="col-sm-1">Department</th>
												<th scope="col" class="col-sm-1">User Name</th>
												<th scope="col" class="col-sm-1">Added On</th>
												<th scope="col" class="col-sm-1 text-center">Status</th>
												<th scope="col" class="col-sm-1 text-center">Order</th>
												<th scope="col" class="col-sm-1 text-center">Action</th>
											</tr>
											</thead>
											<tbody>
											@foreach($assignees as $index => $assignee)
												<tr>
													<td>{{ $index + 1 }}</td>
													<td>{{ $assignee->category ? $assignee->category->name : 'For All Catgs.' }}</td>
													<td>{{ $assignee->programme ? $assignee->programme->name : 'For All Progs.' }}</td>
													<td>{{ $assignee->designation ? $assignee->designation->name: 'For All Desgs.' }}</td>
													<td>{{ $assignee->department ? $assignee->department->team_name : 'For All Depts.' }}</td>
													<td>{{ $assignee->user ? $assignee->user->name : 'For All Users' }}</td>
													<td>{{ \Carbon\Carbon::parse($assignee->created_at)->format('jS M, Y') }}</td>
													<td class="text-center"><i class="fas fa-circle {{ $assignee->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
													<td class="text-center">{{ $assignee->order }}</td>
													<td class="d-flex justify-content-center px-2">
														<div class="dropdown">
															<button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="top" title="Click to manage assignee">
																<i class="fa fa-question-circle mr-1"></i>
															</button>
															<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
																<a class="dropdown-item btn btn-outline-success btn-sm f-14 p-2 mr-3 openRightModal" href="{{ route('config.editAssignee', ['id' => $assignee->id]) }}"><i class="fa fa-plus mr-1"></i> Edit</a>
																<button type="button" data-id="{{ $assignee->id }}" class="dropdown-item btn btn-outline-danger btn-sm  f-14 p-2 mr-3 deleteAssignee"><i class="fa fa-trash mr-1"></i> Delete</button>
															</div>
														</div>
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

					<div class="tab-pane fade" id="configurator" role="tabpanel" aria-labelledby="configurator-tab">
						<div class="flex-grow-1 align-items-center p-3 border-grey bg-white">
							<div class="row my-2">
								<div class="col-sm-12 d-flex align-items-center">
									<p class="m-0 f-21 font-weight-normal text-capitalize"><i class="fa fa-file-alt"></i> @lang('trainingpro::app.header.configuratorTitle')</p>
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
														<h4 class="mb-0">@lang('trainingpro::app.header.categories'):</h4>
														<button type="button" class="btn text-dark btn-sm" id="addCategory" data-toggle="modal" data-target="#addCategoryFormModal">
															<i class="fa fa-plus"></i> @lang('trainingpro::app.button.addCategory')
														</button>
													</div>
													<div class="col table-responsive p-0">
														@if(count($tpCategories) != 0)
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
																@foreach($tpCategories as $index => $tpCategory)
																	<tr>
																		<td>{{ $index + 1 }}</td>
																		<td>
																			<strong>{{ $tpCategory->name }}</strong><br>
																			<small>{{ $tpCategory->description }}</small>
																		</td>
																		<td class="text-center"><i class="fas fa-circle {{ $tpCategory->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
																		<td class="d-flex justify-content-end px-2">
																			<button type="button" data-id="{{ $tpCategory->id }}" class="btn btn-outline-success btn-sm mx-2 editCategory"><i class="fa fa-edit"></i></button>
																			<button type="button" data-id="{{ $tpCategory->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteCategory"><i class="fa fa-trash"></i></button>
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
												<!-- PROGRAMME WRAPPER START -->
												<div class="col-12 px-1 mt-3">
													<div class="d-flex align-items-center justify-content-between border-bottom border-secondary bg-light p-2">
														<h4 class="mb-0">@lang('trainingpro::app.header.programmes'):</h4>
														<button type="button" class="btn text-dark btn-sm" id="addProgramme" data-toggle="modal" data-target="#addProgrammeFormModal">
															<i class="fa fa-plus"></i> @lang('trainingpro::app.button.addProgramme')
														</button>
													</div>
													<div class="col table-responsive p-0">
													    <?php
													    //dd($tpProgrammes);
													    ?>
														@if(count($tpProgrammes) != 0)
															<table class="table">
																<thead class="">
																<tr>
																	<th scope="col" class="col-sm-1">#</th>
																	<th scope="col" class="col-sm-5">Name/Description</th>
																	<th scope="col" class="col-sm-1 text-center">Duration</th>
																	<th scope="col" class="col-sm-1 text-center">Order</th>
																	<th scope="col" class="col-sm-1 text-center">Status</th>
																	<th scope="col" class="col-sm-3"></th>
																</tr>
																</thead>
																<tbody>
																@foreach($tpProgrammes as $index => $tpProgramme)
																	<tr>
																		<td>{{ $index + 1 }}</td>
																		<td>
																			<strong>{{ $tpProgramme->name }}</strong> <small>({{ $tpProgramme->name }})</small><br>
																			<small>{{ $tpProgramme->description }}</small>
																		</td>
																		<td class="text-center">{{ $tpProgramme->duration }}</td>
																		<td class="text-center">{{ $tpProgramme->order }}</td>
																		<td class="text-center"><i class="fas fa-circle {{ $tpProgramme->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>

																		<td class="d-flex justify-content-end px-2">
																			<button type="button" data-id="{{ $tpProgramme->id }}" class="btn btn-outline-success btn-sm mx-2 editProgramme"><i class="fa fa-edit"></i></button>
																			<button type="button" data-id="{{ $tpProgramme->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteProgramme"><i class="fa fa-trash"></i></button>
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
											<!-- TOPIC WRAPPER START -->
											<div class="col-7 px-0 mt-3">
												<div class="col-12 px-1 mt-3">
													<div class="d-flex align-items-center justify-content-between border-bottom border-secondary bg-light p-2">
														<h4 class="mb-0">@lang('trainingpro::app.header.topics'):</h4>
														<button type="button" class="btn text-dark btn-sm" id="addTopic" data-toggle="modal" data-target="#addTopicFormModal">
															<i class="fa fa-plus"></i> @lang('trainingpro::app.button.addTopic')
														</button>
													</div>
													<div class="col table-responsive p-0">
														@if(count($tpTopics) != 0)
															<table class="table">
																<thead class="">
																<tr>
																	<th scope="col" class="col-sm-1">#</th>
																	<th scope="col" class="col-sm-5">Name/Description</th>
																	<th scope="col" class="col-sm-2">Content</th>
																	<th scope="col" class="col-sm-1 text-center">Order</th>
																	<th scope="col" class="col-sm-1">Status</th>
																	<th scope="col" class="col-sm-2"></th>
																</tr>
																</thead>
																<tbody>
																@foreach($tpTopics as $index => $tpTopic)
																	<tr>
																		<td>{{ $index + 1 }}</td>
																		<td>
																			<strong>{{ $tpTopic->name }}</strong><br>
																			<span class="f-12 text-info">({{ $tpTopic->programmes->category->name }}/{{ $tpTopic->programmes->name }})</span><br>
																			<small>{{ $tpTopic->description }}</small>
																		</td>
																		<td>
																			<strong class="">{{ ucfirst(strtolower($tpTopic->type)) }}</strong><br/>
																			<small class="text-info">{{ $tpTopic->value }}</small>
																		</td>
																		<td class="text-center">{{ $tpTopic->order }}</td>
																		<td class="text-center"><i class="fas fa-circle {{ $tpTopic->is_enabled ? 'text-success' : 'text-danger' }}"></i></td>
																		<td class="d-flex justify-content-end px-2">
																			<button type="button" data-id="{{ $tpTopic->id }}" class="btn btn-outline-success btn-sm mx-2 editTopic"><i class="fa fa-edit"></i></button>
																			<button type="button" data-id="{{ $tpTopic->id }}" class="btn btn-outline-danger btn-sm mx-2 deleteTopic"><i class="fa fa-trash"></i></button>
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
					<h5 class="modal-title" id="addAssessmentFormModalLabel">@lang('trainingpro::app.header.addAssessment')</h5>
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
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessCategory">Select Category<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="tpAssessCategory" id="tpAssessCategory" data-live-search="false" data-size="8">
										<option>--</option>
										@if(count($tpCategories) != 0)
											@foreach($tpCategories as $tpCategory)
												<option value="{{ $tpCategory->id }}">{{ $tpCategory->name }}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="col-5 form-group my-3">
								<div class="select-box py-2 px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessProgramme">Select Programme<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="tpAssessProgramme" id="tpAssessProgramme" data-live-search="false" data-size="8">
										<option>--</option>
									</select>
								</div>
							</div>
							{{--							<div class="col-3 form-group my-3">--}}
							{{--								<div class="select-box py-2 px-0 mr-3 w-100">--}}
							{{--									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessTopic">Select Topic<sup class="f-14 mr-1">*</sup></label>--}}
							{{--									<select class="form-control select-picker" name="tpAssessTopic" id="tpAssessTopic" data-live-search="false" data-size="8">--}}
							{{--										<option>--</option>--}}
							{{--									</select>--}}
							{{--								</div>--}}
							{{--							</div>--}}
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
						<div class="form-row">
							<div class="col-6 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessment">Assessment Name<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpAssessment" class="form-control height-35 f-14" placeholder="e.g. Basic assessment" value="" name="tpAssessment" autocomplete="off" required>
							</div>
							<div class="col-2 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessMaxScore">Max. Score<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="tpAssessMaxScore" class="form-control height-35 f-14" placeholder="e.g. 100" value="" name="tpAssessMaxScore" autocomplete="off" required>
							</div>
							<div class="col-2 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessMinScore">Min. Score<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="tpAssessMinScore" class="form-control height-35 f-14" placeholder="e.g. 70" value="" name="tpAssessMinScore" autocomplete="off" required>
							</div>
							<div class="col-2 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessDuration">Duration<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="tpAssessDuration" class="form-control height-35 f-14" placeholder="e.g. 30" value="" name="tpAssessDuration" autocomplete="off" required>
							</div>
						</div>
						<div class="form-row">
							<div class="col-9 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessDescription">Programme Description<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpAssessDescription" class="form-control height-35 f-14" placeholder="e.g. Basic assessment for new resources" value="" name="tpAssessDescription" autocomplete="off" required>
							</div>
							<div class="col-3 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpAssessOrder">Enter Topic Order<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="tpAssessOrder" class="form-control height-35 f-14" placeholder="e.g. 0" value="" name="tpAssessOrder" autocomplete="off" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer d-flex justify-content-between">
					<span class="modal-footer-info text-info small">
						<i class="fas fa-info-circle fa-lg rounded-circle"></i> Instruction:<br>
						Use '0' (numeric zero) for no time restriction. Durations are in minutes.
					</span>
					<div class="d-flex">
						<button type="button" class="btn btn-secondary btn-sm mr-1" data-dismiss="modal">@lang('trainingpro::app.button.cancelLimit')</button>
						<button type="button" id="btnSaveAssessment" class="btn btn-primary btn-sm">@lang('trainingpro::app.button.saveAssessment')</button>
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
					<h5 class="modal-title" id="addCategoryFormModalLabel">@lang('trainingpro::app.header.addCategory')</h5>
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
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('trainingpro::app.button.cancelLimit')</button>
					<button type="button" id="btnSaveCategory" class="btn btn-primary">@lang('trainingpro::app.button.saveCategory')</button>
				</div>
			</div>
		</div>
	</div>
	<!-- ADD PROGRAMME MODAL -->
	<div class="modal fade" id="addProgrammeFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addProgrammeFormModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addProgrammeFormModalLabel">@lang('trainingpro::app.header.addProgramme')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="addProgrammeForm">
						@csrf
						<div class="form-row">
							<div class="col-4 form-group my-3">
								<input type="hidden" name="tpProgrammeId" id="tpProgrammeId" value="">
								<div class="select-box px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpProCategory">Select Category<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="tpProCategory" id="tpProCategory" data-live-search="false" data-size="8">
										<option>--</option>
										@if(count($tpCategories) != 0)
											@foreach($tpCategories as $tpCategory)
												<option value="{{ $tpCategory->id }}">{{ $tpCategory->name }}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="col-4 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpProgramme">Programme Name<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpProgramme" class="form-control height-35 f-14" placeholder="e.g. Introduction" value="" name="tpProgramme" autocomplete="off" required>
							</div>
							<div class="col-4 form-group my-3 d-flex align-items-end justify-content-center">
								<div class="custom-control custom-switch d-flex align-items-center justify-content-center">
									<span class="mr-2 deactivate-text">Deactivate</span>
									<div class="custom-control custom-switch">
										<input id="tpProStatus" class="custom-control-input" type="checkbox" name="tpStatus" value="">
										<label class="custom-control-label" for="tpProStatus">Activate</label>
									</div>
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="col-6 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpProDescription">Programme Description<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpProDescription" class="form-control height-35 f-14" placeholder="e.g. Basic introduction of organisation" value="" name="tpProDescription" autocomplete="off" required>
							</div>
							<div class="col-3 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpProDuration">Programme Duration<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="tpProDuration" class="form-control height-35 f-14" placeholder="Duration of programme e.g. 90(in mins)" value="" name="tpProDuration" autocomplete="off" required>
							</div>
							<div class="col-3 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpProOrder">Programme Order<sup class="f-14 mr-1">*</sup></label>
								<input type="number" id="tpProOrder" class="form-control height-35 f-14" placeholder="Display order of programme e.g. 0" value="" name="tpProOrder" autocomplete="off" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('trainingpro::app.button.cancelLimit')</button>
					<button type="button" id="btnSaveProgramme" class="btn btn-primary">@lang('trainingpro::app.button.saveProgramme')</button>
				</div>
			</div>
		</div>
	</div>
	<!-- ADD TOPIC MODAL -->
	<div class="modal fade" id="addTopicFormModal" data-backdrop="static" tabindex="-1" aria-labelledby="addTopicFormModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addTopicFormModalLabel">@lang('trainingpro::app.header.addTopic')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form id="addTopicForm">
						@csrf
						<div class="form-row">
							<div class="col-4 form-group my-3">
								<input type="hidden" name="tpTopicId" id="tpTopicId" value="">
								<div class="select-box px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopCategory">Select Category<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="tpTopCategory" id="tpTopCategory" data-live-search="false" data-size="8">
										<option>--</option>
										@if(count($tpCategories) != 0)
											@foreach($tpCategories as $tpCategory)
												<option value="{{ $tpCategory->id }}">{{ $tpCategory->name }}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="col-4 form-group my-3">
								<div class="select-box px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopProgramme">Select Programme<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="tpTopProgramme" id="tpTopProgramme" data-live-search="false" data-size="8">
										<option>--</option>
									</select>
								</div>
							</div>
							<div class="col-4 form-group my-3 d-flex align-items-end justify-content-center">
								<div class="custom-control custom-switch d-flex align-items-center justify-content-center">
									<span class="mr-2 deactivate-text">Deactivate</span>
									<div class="custom-control custom-switch">
										<input id="tpTopStatus" class="custom-control-input" type="checkbox" name="tpTopStatus" value="">
										<label class="custom-control-label" for="tpTopStatus">Activate</label>
									</div>
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="col-4 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopic">Topic Name<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpTopic" class="form-control height-35 f-14" placeholder="e.g. Work Culture" value="" name="tpTopic" autocomplete="off" required>
							</div>
							<div class="col-4 form-group my-3">
								<div class="select-box px-0 mr-3 w-100">
									<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopicType">Topic Type<sup class="f-14 mr-1">*</sup></label>
									<select class="form-control select-picker" name="tpTopicType" id="tpTopicType" data-live-search="false" data-size="8">
										<option>--</option>
										<option value="video">Video</option>
										<option value="pdf">PDF Document</option>
										<option value="presentation">Slideshow/Presentation</option>
									</select>
								</div>
							</div>
							<div class="col-4 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopicValue">Topic Value (link only)<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpTopicValue" class="form-control height-35 f-14" placeholder="e.g. https://www.youtube.com/watch?v=3BOY9SKqbvM" value="" name="tpTopicValue" autocomplete="off" required>
							</div>
						</div>
						<div class="form-row">
							<div class="col-9 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopDescription">Topic Description<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpTopDescription" class="form-control height-35 f-14" placeholder="e.g. Work culture in our company." value="" name="tpTopDescription" autocomplete="off" required>
							</div>
							<div class="col-3 form-group my-3">
								<label class="f-14 text-dark-grey mb-12" data-label="true" for="tpTopOrder">Enter Topic Order<sup class="f-14 mr-1">*</sup></label>
								<input type="text" id="tpTopOrder" class="form-control height-35 f-14" placeholder="Display order of topics e.g. 0" value="" name="tpTopOrder" autocomplete="off" required>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('trainingpro::app.button.cancelLimit')</button>
					<button type="button" id="btnSaveTopic" class="btn btn-primary">@lang('trainingpro::app.button.saveTopic')</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('scripts')
	<script>
		$(document).ready(function () {
			$('#btnSaveCategory').click(function () {
				var url = "{{ route('config.createCategory') }}";
				// Get form data
				var formData = $('#addCategoryForm').serialize();
				if (!$("#tpStatus").is(":checked")) {
					formData += "&tpStatus=0";
				} else {
					formData += "&tpStatus=1";
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
			$('.editCategory').on('click', function () {
				var id = $(this).data('id');
				var url = '{{ route("config.editCategory", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {

						// Populate the modal fields
						$('#tpCategoryId').val(response.tpCategoryId);
						$('#tpCategory').val(response.tpCategory);
						$('#tpDescription').val(response.tpDescription);
						$('#tpStatus').prop('checked', response.tpStatus === 1);

						// Show the modal
						$('#addCategoryFormModal').modal('show');
					},
					error: function (request, status, error) {
						console.log('AJAX error:', error);
					}
				});
			});
			$('.deleteCategory').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("config.destroy", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			$('#btnSaveProgramme').click(function () {
				var url = "{{ route('config.createProgramme') }}";
				var form = $('#addProgrammeForm')[0];
				if (form.checkValidity()) {
					// Get form data
					var formData = $('#addProgrammeForm').serialize();
					if (!$("#tpProStatus").is(":checked")) {
						formData += "&tpProStatus=0";
					} else {
						formData += "&tpProStatus=1";
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
									$('#addProgrammeFormModal').modal('hide');
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
			$('.editProgramme').on('click', function () {
				var id = $(this).data('id');
				var url = '{{ route("config.editProgramme", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {
						// Populate the modal fields
						$('#tpProgrammeId').val(response.tpProgrammeId);
						$('#tpProCategory').val(response.tpProCategory);
						$('#tpProgramme').val(response.tpProgramme);
						$('#tpProDescription').val(response.tpProDescription);
						$('#tpProDuration').val(response.tpProDuration);
						$('#tpProOrder').val(response.tpProOrder);
						$('#tpProStatus').prop('checked', response.tpProStatus === 1);
						// Show the modal
						$('#addProgrammeFormModal').modal('show');
					},
					error: function (request, status, error) {
						console.log('AJAX error:', error);
					}
				});
			});
			$('.deleteProgramme').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("config.destroyProgramme", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			$('#btnSaveTopic').click(function () {
				var url = "{{ route('config.createTopic') }}";
				var form = $('#addTopicForm')[0];
				if (form.checkValidity()) {
					// Get form data
					var formData = $('#addTopicForm').serialize();
					if (!$("#tpTopStatus").is(":checked")) {
						formData += "&tpTopStatus=0";
					} else {
						formData += "&tpTopStatus=1";
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
									$('#addTopicFormModal').modal('hide');
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
			$('.editTopic').on('click', function () {
				var id = $(this).data('id');
				var url = '{{ route("config.editTopic", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {

						// Populate the modal fields
						$('#tpTopicId').val(response.tpTopicId);
						$('#tpTopCategory').val(response.tpTopCategory);
						$('#tpTopic').val(response.tpTopic);
						$('#tpTopicType').val(response.tpTopicType);
						$('#tpTopicValue').val(response.tpTopicValue);
						$('#tpTopDescription').val(response.tpTopDescription);
						$('#tpTopOrder').val(response.tpTopOrder);
						$('#tpTopStatus').prop('checked', response.tpTopStatus === 1);
						refreshSelectPicker('tpTopCategory');
						refreshSelectPicker('tpTopicType');
						populateProgrammes(response.tpTopCategory, targetSelect = '#tpTopProgramme', response.tpTopProgramme);

						// Show the modal
						$('#addTopicFormModal').modal('show');
					},
					error: function (request, status, error) {
						console.log('AJAX error:', error);
					}
				});
			});
			$('.deleteTopic').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("config.destroyTopic", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			$('#btnSaveAssessment').click(function () {
				var url = "{{ route('config.createAssessment') }}";
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
				var url = '{{ route("config.editAssessment", ['id' => 'placeholder']) }}'.replace('placeholder', id);

				// AJAX request
				$.ajax({
					url: url,
					type: 'get',
					success: function (response) {
						// Populate the modal fields
						$('#tpAssessmentId').val(response.tpAssessmentId);
						$('#tpAssessCategory').val(response.tpAssessCategory);
						$('#tpAssessment').val(response.tpAssessment);
						$('#tpAssessMaxScore').val(response.tpAssessMaxScore);
						$('#tpAssessMinScore').val(response.tpAssessMinScore);
						$('#tpAssessDuration').val(response.tpAssessDuration);
						$('#tpAssessDescription').val(response.tpAssessDescription);
						$('#tpAssessOrder').val(response.tpAssessOrder);
						$('#tpAssessStatus').prop('checked', response.tpAssessStatus === 1);
						refreshSelectPicker('tpAssessCategory');
						populateProgrammes(response.tpAssessCategory, targetSelect = '#tpAssessProgramme', response.tpAssessCategory);
						// populateTopics(response.tpAssessProgramme, response.tpAssessTopic);
						// Show the modal
						$('#addAssessmentFormModal').modal('show');
					},
					error: function (request, status, error) {
						console.log('AJAX error:', error);
					}
				});
			});
			$('.deleteAssessment').on('click', function () {
				var id = $(this).data('id');
				var token = $('meta[name="csrf-token"]').attr('content');
				var url = '{{ route("config.destroyAssessment", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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

			// Function to populate programmes based on selected category
			function populateProgrammes(categoryId, targetSelect = '#tpTopProgramme', selectedProgrammeId = null) {
				const token = $('meta[name="csrf-token"]').attr('content');
				const url = '{{ route("config.getProgrammes", ['id' => 'placeholder']) }}'.replace('placeholder', categoryId);

				$.ajax({
					url: url,
					type: 'get',
					headers: {
						'X-CSRF-TOKEN': token
					},
					success: function (data) {
						$(targetSelect).empty().append('<option>Select Programme</option>');
						$('#tpAssessTopic').empty().append('<option>Select Topic</option>');

						$(data).each(function (index, programme) {
							const option = $('<option>', {
								value: programme.tpProId,
								text: programme.tpProName
							});

							if (selectedProgrammeId === programme.tpProId) {
								option.prop('selected', true);
							}

							$(targetSelect).append(option);
						});
						refreshSelectPicker('tpTopProgramme');
						refreshSelectPicker('tpAssessProgramme');
					},
					error: function (request, status, error) {
						console.error('AJAX error:', error);
					}
				});
			}

			// Function to populate topics based on selected programme
			function populateTopics(programmeId, selectedProgrammeId = null) {
				const token = $('meta[name="csrf-token"]').attr('content');
				const url = '{{ route("config.getTopics", ['id' => 'placeholder']) }}'.replace('placeholder', programmeId);

				$.ajax({
					url: url,
					type: 'get',
					headers: {
						'X-CSRF-TOKEN': token
					},
					success: function (data) {
						$("#tpAssessTopic").empty().append('<option>Select Topic</option>');

						$(data).each(function (index, topic) {
							const option = $('<option>', {
								value: topic.tpTopicId,
								text: topic.tpTopicName
							});

							if (selectedProgrammeId === topic.tpTopicId) {
								option.prop('selected', true);
							}

							$("#tpAssessTopic").append(option);
						});
						refreshSelectPicker('tpAssessTopic');
					},
					error: function (request, status, error) {
						console.error('AJAX error:', error);
					}
				});
			}

			// Function to refresh Bootstrap SelectPicker
			function refreshSelectPicker(selectId) {
				$('#' + selectId).selectpicker('refresh');
			}

			// Combine change events and make them more concise
			$(document).on('change', '#tpTopCategory, #tpAssessCategory', function () {
				const selectedCategoryId = $(this).val();
				const targetSelect = this.id === 'tpTopCategory' ? '#tpTopProgramme' : '#tpAssessProgramme';

				if (selectedCategoryId) {
					populateProgrammes(selectedCategoryId, targetSelect);
					refreshSelectPicker('tpAssessProgramme');
					refreshSelectPicker('tpAssessTopic');
				} else {
					$(targetSelect).empty();
				}
			});
			$(document).on('change', '#tpAssessProgramme', function () {
				const selectedCategoryId = $(this).val();

				if (selectedCategoryId) {
					populateTopics(selectedCategoryId);
					refreshSelectPicker('tpAssessTopic');
				} else {
					$('#tpAssessProgramme').empty();
				}
			});

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
		$('body').on('click', '.deleteAssignee', function () {
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
					var url = '{{ route("config.destroyAssignee", ['id' => 'placeholder']) }}'.replace('placeholder', id);
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
@endpush
