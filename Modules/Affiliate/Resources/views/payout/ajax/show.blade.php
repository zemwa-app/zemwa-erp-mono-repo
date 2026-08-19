<div class="row">
    <div class="col-sm-12">
        <x-cards.data :title="__('affiliate::app.payoutDetail')" class=" mt-4">
            <x-cards.data-row :label="__('affiliate::app.affiliateName')" :value="$payout->affiliate->user->name" />

            <x-cards.data-row :label="__('affiliate::app.balance')" :value="global_currency_format($payout->balance)" />

            <x-cards.data-row :label="__('affiliate::app.amountRequested')"
                :value="global_currency_format($payout->amount_requested)" />

            <x-cards.data-row :label="__('affiliate::app.paymentMethod')"
                :value="$payout->payment_method ? $payout->payment_method->label() : ''" />

            @if ($payout->payment_method == \Modules\Affiliate\Enums\PayoutMethod::Other)
                <x-cards.data-row :label="__('affiliate::app.otherPaymentMethod')" :value="$payout->other_payment_method" />
            @endif

            <x-cards.data-row :label="__('app.note')" :value="$payout->note" />

            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                    @lang('app.status')</p>
                <p class="mb-0 text-dark-grey f-14 w-70">
                    @if ($payout->status == \Modules\Affiliate\Enums\PaymentStatus::Paid)
                        <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                    @elseif ($payout->status == \Modules\Affiliate\Enums\PaymentStatus::Pending)
                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                    @else
                        <i class="fa fa-circle mr-1 text-red f-10"></i>
                    @endif
                    {{ $payout->status->label() }}
                </p>
            </div>

            <x-cards.data-row :label="__('affiliate::app.paidAt')" :value="$payout->paid_at ? Carbon\Carbon::parse($payout->paid_at)->format(global_setting()->date_format) : '-'" />
            <x-cards.data-row :label="__('app.createdAt')" :value="$payout->created_at->format(global_setting()->date_format)" />

            @if ($payout->status == \Modules\Affiliate\Enums\PaymentStatus::Paid)
                <x-cards.data-row :label="__('app.transactionId')"
                :value="$payout->transaction_id ?? '-'" />

                <x-cards.data-row :label="__('modules.timeLogs.memo')"
                :value="$payout->memo ?? '-'" />
            @endif
        </x-cards.data>
    </div>
</div>
