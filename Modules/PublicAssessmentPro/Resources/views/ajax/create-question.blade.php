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
        <x-form id="createQaAnsForm">
            <div class="add-client bg-white rounded">
                <h5 class="mb-0 p-20 font-weight-normal border-bottom-grey">@lang('publicassessmentpro::app.header.qaListingHeader') "{{ $paAssessment->assessment_name }}"</h5>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <div class="col-sm-12 col-md-8 col-lg-9">
                                <x-forms.label class="my-3" fieldId="assessQaCategory" fieldLabel="Question Category" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="assessQaCategory"
                                            id="assessQaCategory" data-live-search="true">
                                        <option value="0">--</option>
                                        @foreach ($papCategories as $paCategory)
                                            <option value="{{ $paCategory->id }}">{{ $paCategory->category_name }}</option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>
                            <div class="col-sm-12 col-md-4 col-lg-3 pb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-block btn-outline-danger btn-sm" id="viewQuestCategory" data-toggle="modal" data-target="#viewQuestCategoryModal"><i class="fa fa-eye"></i>  @lang('publicassessmentpro::app.button.viewQuestCategory')</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row p-20">
                    <div class="col-lg-12">
                        @if ($paAssessment->assessment_type==0)
                            <div class="row">
                                <div class="col-sm-12 col-md-10 col-lg-10 col-xl-10">
                                    <input type="hidden" name="quesId" value="{{ $quesId }}">
                                    <input type="hidden" name="assessmentId" value="{{ $paAssessment->id }}">
                                    <x-forms.text fieldId="assesQa" :fieldLabel="__('publicassessmentpro::app.form.assesQuestion')"
                                                fieldName="assesQa" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderQuestion')">
                                    </x-forms.text>
                                </div>

                                <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2">
                                    <x-forms.number fieldId="assesMark" :fieldLabel="__('publicassessmentpro::app.form.assesMark')"
                                                    fieldName="assesMark" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderMark')">
                                    </x-forms.number>
                                </div>

                                <div class="col-lg-6 col-md-6 col-xl-3">
                                    <x-forms.input-group class="d-flex align-items-end">
                                        <x-forms.text fieldId="assesAnsOne" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerOne')"
                                                    fieldName="assesAnsOne" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsOne')">
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
                                        <x-forms.text fieldId="assesAnsTwo" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerTwo')"
                                                    fieldName="assesAnsTwo" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsTwo')">
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
                                        <x-forms.text fieldId="assesAnsThree" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerThree')"
                                                    fieldName="assesAnsThree" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsThree')">
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
                                        <x-forms.text fieldId="assesAnsFour" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerFour')"
                                                    fieldName="assesAnsFour" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsFour')">
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
                        @elseif($paAssessment->assessment_type==1)
                            <div class="row">
                                <div class="col-sm-12">
                                    <input type="hidden" name="quesId" value="{{ $quesId }}">
                                    <input type="hidden" name="assessmentId" value="{{ $paAssessment->id }}">
                                    <x-forms.text fieldId="assesQa" :fieldLabel="__('publicassessmentpro::app.form.assesQuestion')"
                                                fieldName="assesQa" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderQuestion')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-6 col-md-6 col-xl-3">
                                    <x-forms.input-group class="d-flex align-items-end">
                                        <x-forms.text fieldId="assesAnsOne" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerOne')"
                                                    fieldName="assesAnsOne" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsOne')">
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
                                        <x-forms.text fieldId="assesAnsTwo" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerTwo')"
                                                    fieldName="assesAnsTwo" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsTwo')">
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
                                        <x-forms.text fieldId="assesAnsThree" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerThree')"
                                                    fieldName="assesAnsThree" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsThree')">
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
                                        <x-forms.text fieldId="assesAnsFour" :fieldLabel="__('publicassessmentpro::app.form.assesAnswerFour')"
                                                    fieldName="assesAnsFour" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderAnsFour')">
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
                        @elseif($paAssessment->assessment_type==2)
                            <div class="row">
                                <div class="col-sm-12">
                                    <input type="hidden" name="quesId" value="{{ $quesId }}">
                                    <input type="hidden" name="assessmentId" value="{{ $paAssessment->id }}">
                                    <x-forms.text fieldId="assesQa" :fieldLabel="__('publicassessmentpro::app.form.assesQuestion')"
                                                fieldName="assesQa" fieldRequired="true" :fieldPlaceholder="__('publicassessmentpro::app.form.placeholderQuestion')">
                                    </x-forms.text>
                                </div>

                            </div>
                        @endif
                        
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
                            <x-forms.button-cancel :link="route('publicassessmentpro.config.home')" class="border mr-3">@lang('publicassessmentpro::app.button.cancelLimit')</x-forms.button-cancel>
                            <x-forms.button-primary id="save-qAns" icon="check">@lang('publicassessmentpro::app.button.saveQans')</x-forms.button-primary>
                        </x-form-actions>
                    </div>
                </div>

            </div>
        </x-form>
    </div>
</div>

<!-- ADD QUESTION CATEGORY MODAL -->
<div class="modal fade" id="viewQuestCategoryModal" data-backdrop="false" tabindex="-1" aria-labelledby="viewQuestCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewQuestCategoryLabel">@lang('publicassessmentpro::app.header.titleQuestCategory')</h5>
                <!--button-- type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button-->
            </div>
            <div class="modal-body">
                 <form id="addQCategoryForm">
                    @csrf
                    <div class="form-row">
                        <div class="col-8 form-group my-3">
                            <label class="f-14 text-dark-grey mb-12" data-label="true" for="paQcategryName">Categoty Name<sup class="f-14 mr-1">*</sup></label>
                            <input type="text" id="paQcategryName" class="form-control height-35 f-14" placeholder="e.g. Basic assessment" value="" name="paQcategryName" autocomplete="off" required>
                        </div>
                        <div class="col-4 form-group my-3 d-flex pt-4 align-items-center">
                            <input type="hidden" id="paQuestCategoryId" name="paQuestCategoryId" value="">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="btnSaveCategory" data-toggle="modal" data-target="#addCategoryFormModal">
                                <i class="fa fa-plus"></i> @lang('publicassessmentpro::app.button.addCategory')
                            </button>
                        </div>
                    </div>
                </form>
                <div class="col table-responsive p-0">
                    @if(count($papCategories) != 0)
                    <table class="table">
                        <thead class="">
                        <tr>
                            <th scope="col" class="col-sm-1">#</th>
                            <th scope="col" class="col-sm-7">Name</th>
                            <th scope="col" class="col-sm-3"></th>
                        </tr>
                        </thead>
                        <tbody id="listQcategory">
                       {{--view : list-qcategory--}}
                        </tbody>
                    </table>
                    @else
                    <div class="alert alert-warning" role="alert">
                        <i class="fa fa-info mx-2"></i> @lang('publicassessmentpro::app.message.noDataFound')
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary closeBtn" id="btnCategoryClose" data-dismiss="modal">@lang('publicassessmentpro::app.button.close')</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {

        refreshSelectPicker('assessQaCategory');
        $("#viewQuestCategory").click(function () {
            loadQuesCategoryList();
        });

          // Function to refresh Bootstrap SelectPicker
        function refreshSelectPicker(selectId) {
            $('#' + selectId).selectpicker('refresh');
           
        }

        $(".btnRightAns input[type='radio']").on("change", function () {
            $(".btnRightAns [data-icon='check']").css("opacity", 0);
            $(this).next("[data-icon='check']").css("opacity", this.checked ? 1 : 0);
        });

        $(".deactivate-text").click(function () {
            $("#tpQsStatus").click();
        });

        $('#save-qAns').click(function() {
            var url = "{{ route ('publicassessmentpro.config.storeQa') }}";
            var formData = $('#createQaAnsForm').serialize();
            if (!$("#tpQsStatus").is(":checked")) {
                formData += "&tpQsStatus=0";
            } else {
                formData += "&tpQsStatus=1";
            }

            $.easyAjax({
                url: url,
                container: '#createQaAnsForm',
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
        // Function to load Question Category list dynamically
        function loadQuesCategoryList() {
            var token = $('meta[name="csrf-token"]').attr('content');
            var url = '{{ route("publicassessmentpro.config.getQuesCategoryList")}}';

            // AJAX request
            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    console.log(response.html);
                    $("#listQcategory").html(response.html)
                },
                error: function (request, status, error) {
                    console.log('AJAX error:', error);
                }
            });

        }
      

        $('#viewQuestCategoryModal .closeBtn').click(function() {
            // Code to execute when the close button is clicked
            $('.preloader-container').addClass('d-flex').show();
            const url = '{{ route("publicassessmentpro.config.getCategories") }}';

            $.easyAjax({
                url: url,
                container: '#createQaAnsForm',
                type: "GET",
                blockUI: true,
                //data: $('#createAssigneeForm').serialize(),
                dataType: "json",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#assessQaCategory').html(response.data);
                        $('#assessQaCategory').selectpicker('refresh');
                    }
                    $('.preloader-container').removeClass('d-flex').hide();
                }
            });


        });

        $('#btnSaveCategory').click(function () {
            var url = "{{ route('publicassessmentpro.config.createCategory') }}";
            // Get form data
            var formData = $('#addQCategoryForm').serialize();
            $("#paQcategryName").val('');
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

                            if (result.isConfirmed) {
                                $("#paQcategryName").val('');
                                 loadQuesCategoryList();
                                // Reload the page after the user clicks OK
                                //location.reload();
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
        $('body').on('click', '.deleteCategory', function () {
            var id = $(this).data('id');
            var token = $('meta[name="csrf-token"]').attr('content');
            var url = '{{ route("publicassessmentpro.config.destroyCategory", ['id' => 'placeholder']) }}'.replace('placeholder', id);

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
                                    loadQuesCategoryList();
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



        init(RIGHT_MODAL);
    });
</script>

