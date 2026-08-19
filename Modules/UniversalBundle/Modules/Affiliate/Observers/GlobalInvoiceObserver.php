<?php

namespace Modules\Affiliate\Observers;

use Modules\Affiliate\Enums\YesNo;
use Modules\Affiliate\Enums\PayoutType;
use App\Models\SuperAdmin\GlobalInvoice;
use Modules\Affiliate\Enums\CommissionType;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Entities\Referral;
use Modules\Affiliate\Enums\PayoutTime;

class GlobalInvoiceObserver
{
    /**
     * Handle the GlobalInvoice "created" event.
     * This observer processes commission calculations when a new invoice is created
     * for a company that was referred by an affiliate.
     *
     * @param GlobalInvoice $invoice The newly created invoice
     * @return void
     */
    public function created(GlobalInvoice $invoice)
    {
        // Only process if the invoice is active
        if ($invoice->status !== 'active') {
            return;
        }

        // Check if this company was referred by an affiliate
        $referral = Referral::where('company_id', $invoice->company_id)->first();

        // Only process if there's a referral and it's not a free package
        if (!$referral || $invoice->package->is_free) {
            return;
        }

        $affiliate = $referral->affiliate;
        $affiliateSettings = AffiliateSetting::first();

        // Verify commission settings are enabled and payout type is after signup
        if (!$this->isCommissionEligible($affiliateSettings)) {
            return;
        }

        // Calculate commission amount based on settings
        $commission = $this->calculateCommission($affiliateSettings, $invoice->total);

        // Process commission based on payout timing
        $this->processCommission($affiliateSettings, $invoice, $affiliate, $referral, $commission);
    }

    public function updated(GlobalInvoice $invoice)
    {
        if ($invoice->status == 'active') {
            $this->created($invoice);
        }
    }

    /**
     * Check if commission should be processed based on settings
     */
    private function isCommissionEligible($affiliateSettings): bool
    {
        return $affiliateSettings->commission_enabled === YesNo::Yes
            && $affiliateSettings->payout_type === PayoutType::AfterSignUp;
    }

    /**
     * Calculate commission amount based on commission type
     */
    private function calculateCommission($affiliateSettings, $invoiceTotal): float
    {
        if ($affiliateSettings->commission_type === CommissionType::Fixed) {
            return $affiliateSettings->commission_cap;
        }

        return $invoiceTotal * $affiliateSettings->commission_cap / 100;
    }

    /**
     * Process and save commission based on payout timing
     */
    private function processCommission($affiliateSettings, $invoice, $affiliate, $referral, $commission): void
    {
        $shouldProcess = false;

        if ($affiliateSettings->payout_time === PayoutTime::OneTime) {
            // Only process on second invoice for one-time payouts
            $shouldProcess = GlobalInvoice::where('company_id', $invoice->company_id)->count() === 2;
        } else if ($affiliateSettings->payout_time === PayoutTime::EveryTime) {
            $shouldProcess = true;
        }

        if ($shouldProcess) {
            $affiliate->balance += $commission;
            $referral->commissions += $commission;

            $affiliate->save();
            $referral->save();
        }
    }
}
