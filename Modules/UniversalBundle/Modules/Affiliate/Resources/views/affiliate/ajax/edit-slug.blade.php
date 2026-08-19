<div class="modal-header">
    <h5 class="modal-title">@lang('app.edit') @lang('affiliate::app.affiliate') @lang('affiliate::app.referralCode')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="edit-affiliate" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-sm-12">
                <x-forms.label class="my-3" fieldId="referral_code" :fieldLabel="__('affiliate::app.referralCode')" :fieldRequired='true'></x-forms.label>
                <x-forms.input-group>
                    <x-slot name="prepend">
                        <span class="input-group-text">{{ route('affiliate.redirectReferral', '') . '/' }}</span>
                    </x-slot>

                    <input type="text" class="form-control height-35 f-14" name="referral_code" id="referral_code"
                        value="{{ $affiliate->referral_code }}">
                </x-forms.input-group>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="mr-3 border-0">@lang('app.close')</x-forms.button-cancel>
        <x-forms.button-primary id="save-affiliate" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>


<script>
"use strict";  // Enforces strict mode for the entire script
    $('body').on('click', '#save-affiliate', function () {
        var url = "{{ route('affiliate-dashboard.update', ':id') }}";
        url = url.replace(':id', '{{ $affiliate->id }}');

        $.easyAjax({
            url: url,
            container: '#edit-affiliate',
            type: "PUT",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-affiliate",
            data: $('#edit-affiliate').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        });
    });
</script>
