<div class="row">
    <div class="col-sm-12">
        <x-form id="add-domain-type-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('app.add') @lang('servermanager::app.domain.domainType')
                </h4>

                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="type" :fieldLabel="__('servermanager::app.domain.type')" fieldName="type"
                                      fieldRequired="true" :fieldPlaceholder="__('servermanager::app.selectDomainType')">
                        </x-forms.text>
                        <small class="text-lightest d-block mt-1">Example: <code>com</code> or <code>in</code> (don’t include the dot).</small>
                    </div>
                </div>

                <x-form-actions class="border-top-grey">
                    <x-forms.button-primary id="save-domain-type" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel data-dismiss="modal">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#save-domain-type').click(function() {
        const url = "{{ route('server-manager.domain-type.store') }}";

        $.easyAjax({
            url: url,
            container: '#add-domain-type-form',
            type: "POST",
            blockUI: true,
            data: $('#add-domain-type-form').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    // Update domain type dropdown in the underlying form (create/edit domain modal)
                    if ($('#domain_type').length && response.optionsHtml) {
                        $('#domain_type').html(response.optionsHtml);
                        $('#domain_type').selectpicker('refresh');
                        $('#domain_type').selectpicker('val', response.type);
                    }

                    $(MODAL_LG).modal('hide');
                }
            }
        });
    });
</script>

