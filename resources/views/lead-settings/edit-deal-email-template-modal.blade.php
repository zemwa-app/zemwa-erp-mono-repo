<x-form id="editDealEmailTemplate" method="PUT" class="ajax-form">
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('app.edit') @lang('modules.deal.emailTemplates')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    </div>

    <div class="modal-body">
        <div class="portlet-body">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"
                            :fieldValue="$template->name" />
                    </div>

                    <div class="col-lg-12">
                        <x-forms.text fieldId="subject" :fieldLabel="__('app.subject')" fieldName="subject" fieldRequired="true"
                            :fieldValue="$template->subject" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="template-body" fieldRequired="true" :fieldLabel="__('app.body')" />
                            <div id="template-body">{!! $template->body !!}</div>
                            <textarea name="body" id="template-body-text" class="d-none">{!! $template->body !!}</textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <p class="f-12 text-lightest mb-1">@lang('modules.deal.mergeFields')</p>
                        <p class="f-12 text-dark-grey">@lang('modules.deal.mergeFieldsHelp')</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="update-deal-email-template" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
    $(document).ready(function() {
        quillImageLoad('#template-body');
    });

    $('#update-deal-email-template').click(function() {
        var note = document.getElementById('template-body').children[0].innerHTML;
        document.getElementById('template-body-text').value = note;

        $.easyAjax({
            url: "{{ route('deal-email-templates.update', $template->id) }}",
            container: '#editDealEmailTemplate',
            type: "POST",
            blockUI: true,
            disableButton: true,
            buttonSelector: "#update-deal-email-template",
            data: $('#editDealEmailTemplate').serialize(),
            success: function(response) {
                if (response.status == "success") {
                    $(MODAL_LG).modal('hide');
                    window.location.href = "{{ route('lead-settings.index') }}?tab=email-templates";
                }
            }
        });
    });
</script>
