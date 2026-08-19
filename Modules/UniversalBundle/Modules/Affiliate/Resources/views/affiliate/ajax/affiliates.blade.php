@php
    $editAffiliatePermission = user()->permission('edit_affiliates');
    $addPayoutPermission = user()->permission('add_payouts');
@endphp

<!-- ROW START -->
<div class="row">
    <!--  WIDGET CARDS START -->
    <div class="col-md-12 col-xl-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <x-cards.widget :title="__('affiliate::app.totalReferrals')" :value="$referrals->count()" icon="user" />
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <x-cards.widget :title="__('affiliate::app.totalEarnings')" :value="$totalEarnings" icon="coins" />
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <x-cards.widget :title="__('affiliate::app.totalPayouts')" :value="$payouts" icon="money-bill" />
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <x-cards.widget :title="__('affiliate::app.currentBalance')" :value="$currentBalance" icon="wallet" />
            </div>
        </div>
    </div>
    <!--  WIDGET CARDS END -->
</div>
<!-- ROW END -->

<!-- ROW START -->
<div class="row mt-4">
    <!--  WIDGET CARDS START -->
    <div class="col-xl-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <x-cards.data :title="__('affiliate::app.affiliateLink')">
            <div class="row">
                @if ($affiliate)

                    <div class="col-md-8">
                        <span class="input-group-text" id="referral-link">{{ route('affiliate.redirectReferral', ['referral' => $affiliate->referral_code])}}</span>
                    </div>
                    <div class="col-md-2 px-0 align-content-center">
                        <a href="javascript:;" class="btn-copy btn-secondary rounded p-1 py-2 ml-1"
                        data-clipboard-text="{{ route('affiliate.redirectReferral', ['referral' => $affiliate->referral_code])}}">
                        <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>

                        @if ($editAffiliatePermission == 'all')
                            <a href="javascript:;" id="edit-affiliate-link" class="btn-secondary rounded p-1 py-2 ml-1">
                                <i class="fa fa-edit mx-1"></i>@lang('app.edit')</a>
                        @endif
                    </div>

                @else
                    <div class="col-md-12">
                        <span class="input-group-text text-center" id="referral-link">@lang('affiliate::messages.affiliateNotRegistered')</span>
                    </div>
                @endif
            </div>
        </x-cards.data>
    </div>
</div>
<!-- ROW END -->

<!-- ROW START -->

@if ($addPayoutPermission == 'all')
    <div class="row mt-4">
        <div class="col-xl-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
            <x-cards.data :title="__('affiliate::app.payoutRequest')">
                <div class="row">
                    <div class="col-sm-12">
                        <x-form id="save-request-data-form">
                            <div class="add-client bg-white rounded">
                                <p class="border-bottom-grey"></p>
                                <div class="row px-3 mb-3">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="hidden" name="affiliate_id" value="{{ $affiliate->id }}">
                                                <x-forms.number fieldId="amount" :fieldLabel="__('app.amount')" fieldName="amount"
                                                    :fieldPlaceholder="__('app.amount')"
                                                    :popover="__('affiliate::messages.minimumPayoutMessage',
                                                    ['min_payout' => global_currency_format($settings->minimum_payout), 'current_balance' => global_currency_format($affiliate->balance)])"
                                                    fieldRequired="true" />
                                            </div>
                                            <div class="col-md-6">
                                                <x-forms.select fieldId="payment_method" :fieldLabel="__('affiliate::app.paymentMethod')"
                                                        :fieldRequired="true" fieldName="payment_method" search="true">
                                                    @foreach (\Modules\Affiliate\Enums\PayoutMethod::cases() as $payoutMethod)
                                                        <option value="{{ $payoutMethod->value }}">
                                                            {{ $payoutMethod->label() }}
                                                        </option>
                                                    @endforeach
                                                </x-forms.select>
                                            </div>
                                            <div class="col-md-6 d-none" id="other_payment_method">
                                                <x-forms.text fieldId="other_payment_method" :fieldLabel="__('affiliate::app.otherPaymentMethod')"
                                                    fieldName="other_payment_method" :fieldRequired="true"
                                                    :fieldPlaceholder="__('affiliate::placeholders.otherPaymentMethod')" />
                                            </div>
                                            <div class="col-md-6">
                                                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.note')"
                                                    fieldName="note" fieldId="note" :fieldPlaceholder="__('affiliate::placeholders.payoutDetails')" fieldRequired="false">
                                                </x-forms.textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <x-form-actions>
                                    <x-forms.button-primary class="mr-3" id="save-payout-request-form"
                                        icon="check">@lang('affiliate::app.request')
                                    </x-forms.button-primary>
                                </x-form-actions>

                            </div>
                        </x-form>

                    </div>
                </div>
            </x-cards.data>
        </div>
        <!--  WIDGET CARDS END -->
    </div>
@endif
<!-- ROW END -->

@push('scripts')
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
    <script>
"use strict";  // Enforces strict mode for the entire script
        $(document).ready(function() {

            $('body').on('click', '#save-payout-request-form', function () {

                let url = "{{ route('payout.store') }}";

                $.easyAjax({
                    url: url,
                    container: '#save-request-data-form',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    file: true,
                    buttonSelector: "#save-payout-request-form",
                    data: $('#save-request-data-form').serialize(),

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
        });
        // Copy to clipboard script start
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            Swal.fire({
                icon: 'success',
                text: '@lang("affiliate::app.affiliateReferralCopied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });
        // Copy to clipboard script end
    </script>
    @if ($affiliate)
        <script>
"use strict";  // Enforces strict mode for the entire script
            $('body').on('click', '#edit-affiliate-link', function () {
                    var id = {{ $affiliate->id }};
                    var url = "{{ route('affiliate-dashboard.edit', ':id') }}";
                    url = url.replace(':id', id);
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, url);
            });
        </script>
    @endif
@endpush
