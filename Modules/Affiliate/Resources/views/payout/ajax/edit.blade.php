@php
    $addPayoutsPermission = user()->permission('add_payouts');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="payout-form" method="PUT">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-3 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('affiliate::app.editWithdrawal')</h4>

                <div class="row px-3">
                    @if ($addPayoutsPermission == 'all')
                        <div class="col-md-4">
                            <x-forms.select fieldId="affiliate_id"
                                            :fieldLabel="__('affiliate::app.affiliateName')"
                                            fieldName="affiliate_id"
                                            search="true" alignRight="true" fieldRequired="true">
                                <option value="">--</option>
                                @foreach ($users as $affiliate)
                                    <x-affiliate::affiliate-option :user="$affiliate->user"
                                    :additionalText="$affiliate->user->clientDetails?->company_name"
                                    :affiliateId="$affiliate->id" :selected="$affiliate->id == $payout->affiliate_id" />
                                @endforeach
                            </x-forms.select>
                        </div>
                    @else
                        <input type="hidden" name="affiliate_id" value="{{ $payout->affiliate_id }}">
                    @endif

                    <div class="col-md-4">
                        <x-forms.number fieldId="amount" :fieldLabel="__('modules.invoices.amount')" fieldName="amount"
                        :popover="__('affiliate::app.minimumPayout') . ' ' . global_currency_format($affiliateSetting->minimum_payout)"
                        :fieldPlaceholder="__('placeholders.price')" :fieldValue="$payout->amount_requested"
                            fieldRequired="true" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="payment_method" :fieldLabel="__('affiliate::app.paymentMethod')"
                                :fieldRequired="true" fieldName="payment_method" search="true">
                            @foreach (\Modules\Affiliate\Enums\PayoutMethod::cases() as $payoutMethod)
                                <option value="{{ $payoutMethod->value }}" @selected($payoutMethod == $payout->payment_method)>
                                    {{ $payoutMethod->label() }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4 d-none" id="other_payment_method">
                        <x-forms.text fieldId="other_payment_method" :fieldLabel="__('affiliate::app.otherPaymentMethod')"
                            fieldName="other_payment_method" :fieldRequired="true" :fieldValue="$payout->other_payment_method"
                            :fieldPlaceholder="__('affiliate::placeholders.otherPaymentMethod')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.textarea fieldId="note" :fieldLabel="__('app.note')" fieldName="note"
                        :fieldValue="$payout->note" :fieldPlaceholder="__('placeholders.note')" />
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

        var paymentMethod = $('#payment_method').val();
        if (paymentMethod == "{{ \Modules\Affiliate\Enums\PayoutMethod::Other }}") {
            $('#other_payment_method').removeClass('d-none');
        }

        $('body').on('click', '#save-payout-form', function () {
            url = "{{ route('payout.update', ':id') }}"
            url = url.replace(':id', {{ $payout->id }}),

            $.easyAjax({
                url: url,
                container: '#payout-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-payout-form",
                data: $('#payout-form').serialize(),
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
