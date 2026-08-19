<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('affiliate::app.confirmPaid')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="confirmPaid">
        <div class="row">
            <div class="col-md-6">
                <x-forms.text fieldId="transaction_id" :fieldLabel="__('app.transactionId')"
                    fieldName="transaction_id" fieldRequired="false" :fieldPlaceholder="__('placeholders.payments.transactionId')">
                </x-forms.text>
            </div>
            <div class="col-sm-12">
                <x-forms.textarea fieldId="memo" :fieldLabel="__('modules.timeLogs.memo')"
                    fieldName="memo" fieldRequired="false" fieldPlaceholder="">
                </x-forms.textarea>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-confirm_paid" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
"use strict";  // Enforces strict mode for the entire script
    $('body').on('click', '#save-confirm_paid', function () {
        var url = "{{ route('payouts.change_status') }}";
        let token = "{{ csrf_token() }}";
        let status = 'paid';

        $.easyAjax({
            url: url,
            type: "POST",
            disableButton: true,
            blockUI: true,
            data: {
                '_token': token,
                id: {{ $payout->id }},
                status: status,
                transaction_id: $('#transaction_id').val(),
                memo: $('#memo').val()
            },
            success: function(response) {

                if (response.status == "success") {
                    if ($(MODAL_LG).hasClass('show')) {
                        $(MODAL_LG).modal('hide');
                        window.location.reload();
                    }
                    showTable();
                }
            }
        })
    });
</script>
