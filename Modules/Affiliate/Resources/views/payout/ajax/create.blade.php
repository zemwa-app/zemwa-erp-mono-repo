<div class="row">
    <div class="col-sm-12">
        <x-form id="payout-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-3 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('affiliate::app.createWithdrawal')</h4>

                <div class="row px-3">
                    <div class="col-md-4">
                        <x-forms.select fieldId="affiliate_id"
                                        :fieldLabel="__('affiliate::app.affiliateName')"
                                        fieldName="affiliate_id"
                                        search="true" alignRight="true" fieldRequired="true">
                            <option value="">--</option>
                            @foreach ($users as $affiliate)
                                <x-affiliate::affiliate-option :user="$affiliate->user" :additionalText="$affiliate->user->clientDetails?->company_name" :affiliateId="$affiliate->id" />
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldId="amount" :fieldLabel="__('modules.invoices.amount')" fieldName="amount"
                        :popover="__('affiliate::messages.minimumPayoutMessage',
                        ['min_payout' => global_currency_format($affiliateSetting->minimum_payout), 'current_balance' => global_currency_format($affiliate->balance)])"
                        :fieldPlaceholder="__('placeholders.price')"
                            fieldRequired="true" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="payment_method" :fieldLabel="__('affiliate::app.paymentMethod')"
                                :fieldRequired="true" fieldName="payment_method" search="true">
                            @foreach (\Modules\Affiliate\Enums\PayoutMethod::cases() as $payoutMethod)
                                <option value="{{ $payoutMethod->value }}">
                                    {{ $payoutMethod->label() }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4 d-none" id="other_payment_method">
                        <x-forms.text fieldId="other_payment_method" :fieldLabel="__('affiliate::app.otherPaymentMethod')"
                            fieldName="other_payment_method" :fieldRequired="true"
                            :fieldPlaceholder="__('affiliate::placeholders.otherPaymentMethod')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.textarea fieldId="note" :fieldLabel="__('app.note')" fieldName="note"
                                          :fieldPlaceholder="__('placeholders.note')" />
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-payout-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('payout.index')"
                                           class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
"use strict";  // Enforces strict mode for the entire script

    $(document).ready(function () {

        $('body').on('click', '#save-payout-form', function () {
            $.easyAjax({
                url: "{{ route('payout.store') }}",
                container: '#payout-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-payout-form",
                data: $('#payout-form').serialize(),
                redirect: true,
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.href = '{{ route('payout.index') }}';
                    }
                }
            });
        });

        $('body').on('change', '#payment_method', function () {
            var paymentMethod = $('#payment_method').val();
            if (paymentMethod == "{{ \Modules\Affiliate\Enums\PayoutMethod::Other }}") {
                $('#other_payment_method').removeClass('d-none');
            } else {
                $('#other_payment_method').addClass('d-none');
            }

        });

        init(RIGHT_MODAL);
    });


</script>
