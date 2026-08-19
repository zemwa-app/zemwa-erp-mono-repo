@php
	$quesId = $questionId ?? '';
@endphp
<style>
	.btn input[type="radio"] {
		position: absolute;
		clip: rect(0, 0, 0, 0);
	}

	.btn [data-icon="check"] {
		opacity: 0;
		transition: opacity 0.3s ease; /* Smooth transition for opacity */
	}

	.btn input[type="radio"]:checked + [data-icon="check"] {
		opacity: 1;
	}

	.btn:hover [data-icon="check"] {
		opacity: 1;
	}
</style>
<div class="row">
	<div class="col-sm-12">
		<x-form id="createQa">
		<div class="add-client bg-white rounded">
			<h5 class="mb-0 p-20 font-weight-normal border-bottom-grey">@lang('trainingpro::app.header.qaListingHeader') "{{ $tpAssessment->name }}"</h5>
			<div class="row p-20">
				<div class="col-lg-12">
					<div class="row">
						<div class="col-sm-12 col-md-10 col-lg-10 col-xl-10">
							<input type="hidden" name="quesId" value="{{ $quesId }}">
							<input type="hidden" name="assessmentId" value="{{ $tpAssessment->id }}">
							<x-forms.text fieldId="assesQa" :fieldLabel="__('trainingpro::app.form.assesQuestion')"
										  fieldName="assesQa" fieldRequired="true" :fieldPlaceholder="__('trainingpro::app.form.placeholderQuestion')">
							</x-forms.text>
						</div>

						<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2">
							<x-forms.number fieldId="assesMark" :fieldLabel="__('trainingpro::app.form.assesMark')"
										  fieldName="assesMark" fieldRequired="true" :fieldPlaceholder="__('trainingpro::app.form.placeholderMark')">
							</x-forms.number>
						</div>

						<div class="col-lg-6 col-md-6 col-xl-3">
							<x-forms.input-group class="d-flex align-items-end">
								<x-forms.text fieldId="assesAnsOne" :fieldLabel="__('trainingpro::app.form.assesAnswerOne')"
											  fieldName="assesAnsOne" fieldRequired="true" :fieldPlaceholder="__('trainingpro::app.form.placeholderAnsOne')">
								</x-forms.text>
								<x-slot name="append">
									<label class="btn btn-outline-light form-control btnRightAns" style="margin-bottom:16px; padding-top:10px; padding-bottom:11px">
										<input type="radio" name="rightAns" value="a1" id="ans-one" autocomplete="off">
										<span class="fa fa-check"></span>
									</label>
								</x-slot>
							</x-forms.input-group>
						</div>

						<div class="col-md-6 col-lg-4 col-xl-3">
							<x-forms.input-group class="d-flex align-items-end">
								<x-forms.text fieldId="assesAnsTwo" :fieldLabel="__('trainingpro::app.form.assesAnswerTwo')"
											  fieldName="assesAnsTwo" fieldRequired="true" :fieldPlaceholder="__('trainingpro::app.form.placeholderAnsTwo')">
								</x-forms.text>
								<x-slot name="append">
									<label class="btn btn-outline-light form-control btnRightAns" style="margin-bottom:16px; padding-top:10px; padding-bottom:11px">
										<input type="radio" name="rightAns" value="a2" id="ans-two" autocomplete="off">
										<span class="fa fa-check"></span>
									</label>
								</x-slot>
							</x-forms.input-group>
						</div>

						<div class="col-md-6 col-lg-4 col-xl-3">
							<x-forms.input-group class="d-flex align-items-end">
								<x-forms.text fieldId="assesAnsThree" :fieldLabel="__('trainingpro::app.form.assesAnswerThree')"
											  fieldName="assesAnsThree" :fieldPlaceholder="__('trainingpro::app.form.placeholderAnsThree')">
								</x-forms.text>
								<x-slot name="append">
									<label class="btn btn-outline-light form-control btnRightAns" style="margin-bottom:16px; padding-top:10px; padding-bottom:11px">
										<input type="radio" name="rightAns" value="a3" id="ans-three" autocomplete="off">
										<span class="fa fa-check"></span>
									</label>
								</x-slot>
							</x-forms.input-group>
						</div>

						<div class="col-md-6 col-lg-4 col-xl-3">
							<x-forms.input-group class="d-flex align-items-end">
								<x-forms.text fieldId="assesAnsFour" :fieldLabel="__('trainingpro::app.form.assesAnswerFour')"
											  fieldName="assesAnsFour" :fieldPlaceholder="__('trainingpro::app.form.placeholderAnsFour')">
								</x-forms.text>
								<x-slot name="append">
									<label class="btn btn-outline-light form-control btnRightAns" style="margin-bottom:16px; padding-top:10px; padding-bottom:11px">
										<input type="radio" name="rightAns" value="a4" id="ans-four" autocomplete="off">
										<span class="fa fa-check"></span>
									</label>
								</x-slot>
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
							<input id="tpQsStatus" class="custom-control-input" type="checkbox" name="tpQsStatus" value="">
							<label class="custom-control-label" for="tpQsStatus" style="cursor:pointer;">Activate</label>
						</div>
					</div>
				</div>
				<!-- Second div with buttons aligned to the right -->
				<div class="col-8">
					<x-form-actions class="d-flex align-items-center justify-content-end">
						<x-forms.button-cancel :link="route('config.home')" class="border mr-3">@lang('trainingpro::app.button.cancelLimit')</x-forms.button-cancel>
						<x-forms.button-primary id="save-qAns" icon="check">@lang('trainingpro::app.button.saveQans')</x-forms.button-primary>
					</x-form-actions>
				</div>
			</div>

		</div>
		</x-form>
	</div>
</div>

<script>
	$(document).ready(function() {

		$(".btnRightAns input[type='radio']").on("change", function () {
			$(".btnRightAns [data-icon='check']").css("opacity", 0);
			$(this).next("[data-icon='check']").css("opacity", this.checked ? 1 : 0);
		});

		$(".deactivate-text").click(function () {
			$("#tpQsStatus").click();
		});

		$('#save-qAns').click(function() {
			var url = "{{ route ('config.storeQa') }}";
			var formData = $('#createQa').serialize();
			if (!$("#tpQsStatus").is(":checked")) {
				formData += "&tpQsStatus=0";
			} else {
				formData += "&tpQsStatus=1";
			}

			$.easyAjax({
				url: url,
				container: '#createQa',
				type: "POST",
				blockUI: true,
				disableButton: true,
				buttonSelector: '#save-qAns',
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
