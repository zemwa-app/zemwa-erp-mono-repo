@php
	$quesId = $questionId ?? '';
@endphp
<div class="row">
	<div class="col-sm-12">
		<x-form id="createAssigneeForm">
			<div class="add-client bg-white rounded">
				<h5 class="mb-0 p-20 font-weight-normal border-bottom-grey">@lang('trainingpro::app.header.createAssignee')</h5>
				<div class="row p-20">
					<div class="col-lg-12">
						<div class="row">
							<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
								<input type="hidden" name="assigneeId" value="{{ $quesId }}">
								<x-forms.select fieldId="department_id" :fieldLabel="__('app.department')"
												fieldName="department_id" fieldRequired="true" search="true">
									<option value="0">--</option>
									@foreach ($departments as $team)
										<option value="{{ $team->id }}">{{ $team->team_name }}</option>
									@endforeach
								</x-forms.select>
							</div>

							<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
								<x-forms.label class="my-3" fieldId="designation_id"
											   :fieldLabel="__('app.designation')" fieldRequired="true">
								</x-forms.label>
								<x-forms.input-group>
									<select class="form-control select-picker" name="designation"
											id="employee_designation" data-live-search="true">
										<option value="0">--</option>
										@foreach ($designations as $designation)
											<option value="{{ $designation->id }}">{{ $designation->name }}</option>
										@endforeach
									</select>
								</x-forms.input-group>
							</div>

							<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
								<div class="form-group my-3">
									<x-forms.label fieldId="selectEmployee" :fieldLabel="__('app.menu.employees')"
												   fieldRequired="true">
									</x-forms.label>
									<x-forms.input-group>
										<select class="form-control select-picker" name="user_id"
												id="selectEmployee" data-live-search="true" data-size="8">
											<option value="0">--</option>
											@foreach ($employees as $item)
												<x-user-option :user="$item" :pill="false"/>
											@endforeach
										</select>
									</x-forms.input-group>
								</div>
							</div>

							<div class="col-lg-5 col-md-5 col-xl-5">
								<x-forms.label class="my-3" fieldId="category_id"
											   fieldLabel="Assignment Category" fieldRequired="true">
								</x-forms.label>
								<x-forms.input-group>
									<select class="form-control select-picker" name="assignment_category"
											id="assignment_category" data-live-search="true">
										<option value="0">--</option>
										@foreach ($categories as $category)
											<option value="{{ $category->id }}">{{ $category->name }}</option>
										@endforeach
									</select>
								</x-forms.input-group>
							</div>

							<div class="col-lg-5 col-md-5 col-xl-5">
								<x-forms.label class="my-3" fieldId="programme_id"
											   fieldLabel="Assignment Programme" fieldRequired="true">
								</x-forms.label>
								<x-forms.input-group>
									<select class="form-control select-picker" name="assignment_programme"
											id="assignment_programme" data-live-search="true">
										<option value="0">--</option>
									</select>
								</x-forms.input-group>
							</div>

							<div class="col-md-2 col-lg-2 col-xl-2">
								<x-forms.input-group class="d-flex align-items-end">
									<x-forms.text fieldId="assignee_order" fieldLabel="Order"
												  fieldName="assignee_order" fieldPlaceholder="e.g. 0">
									</x-forms.text>
								</x-forms.input-group>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<!-- First div content aligned to the left -->
					<div class="col-4 d-flex align-items-center justify-content-start">
						<div class="custom-control custom-switch d-flex align-items-center justify-content-center">
							<span class="mr-2 deactivate-text" style="cursor:pointer;">Deactivate</span>
							<div class="custom-control custom-switch">
								<input id="assignee_status" class="custom-control-input" type="checkbox" name="assignee_status" value="">
								<label class="custom-control-label" for="assignee_status" style="cursor:pointer;">Activate</label>
							</div>
						</div>
					</div>
					<!-- Second div with buttons aligned to the right -->
					<div class="col-8">
						<x-form-actions class="d-flex align-items-center justify-content-end">
							<x-forms.button-cancel :link="route('config.home')" class="border mr-3">@lang('trainingpro::app.button.cancelLimit')</x-forms.button-cancel>
							<x-forms.button-primary id="save-assignee" icon="check">@lang('trainingpro::app.button.saveQans')</x-forms.button-primary>
						</x-form-actions>
					</div>
				</div>

			</div>
		</x-form>
	</div>
</div>

<script>
	$(document).ready(function() {

		$("#selectEmployee").selectpicker({
			actionsBox: true,
			selectAllText: "{{ __('modules.permission.selectAll') }}",
			deselectAllText: "{{ __('modules.permission.deselectAll') }}",
			multipleSeparator: " ",
			selectedTextFormat: "count > 8",
			countSelectedText: function(selected, total) {
				return selected + " {{ __('app.membersSelected') }} ";
			}
		});

		$('#department_id').change(function() {
			var id = $(this).val();
			var desigId = $("#employee_designation").val();
			var url = "{{ route('config.by_department', ['id' => ':id', 'desigId' => ':desigId']) }}";
			url = url.replace(':id', id).replace(':desigId', desigId);

			$.easyAjax({
				url: url,
				container: '#createAssigneeForm',
				type: "GET",
				blockUI: true,
				//data: $('#createAssigneeForm').serialize(),
				dataType: "json",
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function(response) {
					if (response.status == 'success') {
						$('#selectEmployee').html(response.data);
						$('#selectEmployee').selectpicker('refresh');
					}
				}
			});
		});

		$('#employee_designation').change(function() {
			var id = $(this).val();
			var deptId = $("#department_id").val();
			var url = "{{ route('config.by_designation', ['id' => ':id', 'deptId' => ':deptId']) }}";
			url = url.replace(':id', id).replace(':deptId', deptId);

			$.easyAjax({
				url: url,
				container: '#createAssigneeForm',
				type: "GET",
				blockUI: true,
				//data: $('#createAssigneeForm').serialize(),
				dataType: "json",
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function(response) {
					if (response.status == 'success') {
						$('#selectEmployee').html(response.data);
						$('#selectEmployee').selectpicker('refresh');
					}
				}
			});
		});

		$('#assignment_category').change(function() {
			var id = $(this).val();
			var url = "{{ route('config.by_category', ['id' => ':id']) }}";
			url = url.replace(':id', id);

			$.easyAjax({
				url: url,
				container: '#createAssigneeForm',
				type: "GET",
				blockUI: true,
				//data: $('#createAssigneeForm').serialize(),
				dataType: "json",
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function(response) {
					if (response.status == 'success') {
						$('#assignment_programme').html(response.data);
						$('#assignment_programme').selectpicker('refresh');
					}
				}
			});
		});

		$('#save-assignee').click(function() {
			var url = "{{ route ('config.storeAssignee') }}";
			var formData = $('#createAssigneeForm').serialize();
			if (!$("#assignee_status").is(":checked")) {
				formData += "&assignee_status=0";
			} else {
				formData += "&assignee_status=1";
			}

			$.easyAjax({
				url: url,
				container: '#createAssigneeForm',
				type: "POST",
				blockUI: true,
				disableButton: true,
				buttonSelector: '#save-save-assignee',
				data: formData,
				success: function(response) {

					if (response.status == 'success') {
						if ($(MODAL_XL).hasClass('show')) {
							$(MODAL_XL).modal('hide');
							window.location.reload();
						} else {
							window.location.href = response.redirectUrl;
						}
					}
				}
			})
		});

		init(RIGHT_MODAL);
	});
</script>
