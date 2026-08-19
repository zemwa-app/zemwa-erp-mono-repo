<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="send-deal-email-form" method="POST" class="ajax-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('modules.deal.sendEmail')
                </h4>

                <div class="row p-20">
                    <div class="col-md-12">
                        <x-forms.select fieldId="deal_email_template_id" :fieldLabel="__('modules.deal.selectTemplate')"
                            fieldName="deal_email_template_id">
                            <option value="">@lang('modules.deal.blankEmail')</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.email fieldId="to" :fieldLabel="__('app.to')" fieldName="to" fieldRequired="true"
                            :fieldPlaceholder="__('placeholders.email')"
                            :fieldValue="$deal->contact->client_email ?? ''" />
                    </div>

                    <div class="col-md-12">
                        <x-forms.text fieldId="cc" :fieldLabel="__('app.cc')" fieldName="cc"
                            :fieldPlaceholder="__('placeholders.email')" />
                    </div>

                    <div class="col-md-12">
                        <x-forms.text fieldId="subject" :fieldLabel="__('app.subject')" fieldName="subject" fieldRequired="true" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="email-body" fieldRequired="true" :fieldLabel="__('app.body')" />
                            <div id="email-body"></div>
                            <textarea name="body" id="email-body-text" class="d-none"></textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <x-forms.file-multiple class="mr-0" :fieldLabel="__('app.menu.addFile')"
                            fieldName="file" fieldId="email-file-upload-dropzone" />
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="send-deal-email-btn" class="mr-3" icon="paper-plane">
                        @lang('app.send')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('deals.show', $deal->id)" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    $(document).ready(function() {
        quillImageLoad('#email-body');

        var emailDropzone = null;
        Dropzone.autoDiscover = false;

        if ($('#email-file-upload-dropzone').length) {
            emailDropzone = new Dropzone("div#email-file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "#",
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
            });
        }

        var dealId = {{ $deal->id }};
        var hasEditedContent = false;

        $('#subject, #email-body').on('input', function() {
            hasEditedContent = true;
        });

        $('#deal_email_template_id').on('change', function() {
            var templateId = $(this).val();

            if (!templateId) {
                $('#subject').val('');
                var container = $('#email-body').get(0);
                if (container && container.__quill) {
                    container.__quill.setContents([]);
                }
                hasEditedContent = false;
                return;
            }

            var applyTemplate = function() {
                $.easyAjax({
                    url: "{{ route('deal-email-templates.fetch') }}",
                    data: {
                        templateId: templateId,
                        dealId: dealId
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#subject').val(response.subject);
                            var container = $('#email-body').get(0);
                            var quill = Quill.find(container);
                            if (quill) {
                                quill.clipboard.dangerouslyPasteHTML(0, response.body);
                            }
                            hasEditedContent = false;
                        }
                    }
                });
            };

            if (hasEditedContent || $('#subject').val() || ($('#email-body-text').val() && $.trim($('#email-body-text').val()) !== '')) {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('modules.deal.overwriteTemplateConfirm')",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "@lang('app.yes')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: {
                        confirmButton: 'btn btn-primary mr-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyTemplate();
                    }
                });
            } else {
                applyTemplate();
            }
        });

        $('#send-deal-email-btn').click(function() {
            var note = document.getElementById('email-body').children[0].innerHTML;
            document.getElementById('email-body-text').value = note;

            if (emailDropzone) {
                var dt = new DataTransfer();
                emailDropzone.getAcceptedFiles().forEach(function(file) {
                    dt.items.add(file);
                });
                var fallbackInput = document.querySelector('#email-file-upload-dropzone input[type="file"]');
                if (fallbackInput) {
                    fallbackInput.files = dt.files;
                }
            }

            $.easyAjax({
                url: "{{ route('deals.send_email.store', $deal->id) }}",
                container: '#send-deal-email-form',
                type: "POST",
                blockUI: true,
                file: true,
                data: $('#send-deal-email-form').serialize(),
                success: function(response) {
                    if (response.status == "success") {
                        if ($(RIGHT_MODAL).hasClass('in')) {
                            document.getElementById('close-task-detail').click();
                            window.location.href = "{{ route('deals.show', $deal->id) }}?tab=emails";
                        } else {
                            window.location.href = "{{ route('deals.show', $deal->id) }}?tab=emails";
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
