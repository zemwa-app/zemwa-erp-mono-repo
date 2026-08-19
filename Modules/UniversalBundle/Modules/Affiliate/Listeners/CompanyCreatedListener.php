<?php

namespace Modules\Affiliate\Listeners;

use App\Events\NewCompanyCreatedEvent;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Entities\Referral;
use Modules\Affiliate\Enums\CommissionType;
use Modules\Affiliate\Enums\PayoutType;
use Modules\Affiliate\Enums\YesNo;

class CompanyCreatedListener
{
    /**
     * Handle the new company created event.
     * Processes affiliate referrals and commissions when a new company is created.
     *
     * @param NewCompanyCreatedEvent $event The company creation event
     * @return void
     */
    public function handle(NewCompanyCreatedEvent $event): void
    {
        // Check if there's a referral code in the session
        if (!session()->has('referralCode')) {
            return;
        }

        // Find the active affiliate with the matching referral code
        $affiliate = $this->findActiveAffiliate(session()->get('referralCode'));

        if (!$affiliate) {
            session()->forget('referralCode');
            return;
        }

        // Create a new referral record
        $referral = $this->createReferral($affiliate, $event->company->id);

        // Process commission if eligible
        $this->processCommission($affiliate, $referral);

        // Clear the referral code from session
        session()->forget('referralCode');
    }

    /**
     * Find an active affiliate by referral code
     */
    private function findActiveAffiliate(string $referralCode): ?Affiliate
    {
        return Affiliate::where('referral_code', $referralCode)
            ->active()
            ->first();
    }

    /**
     * Create a new referral record
     */
    private function createReferral(Affiliate $affiliate, int $companyId): Referral
    {
        $referral = new Referral();
        $referral->affiliate_id = $affiliate->id;
        $referral->company_id = $companyId;
        $referral->ip = request()->getClientIp();
        $referral->user_agent = request()->userAgent();
        $referral->save();

        return $referral;
    }

    /**
     * Process commission if all conditions are met
     */
    private function processCommission(Affiliate $affiliate, Referral $referral): void
    {
        $settings = AffiliateSetting::first();

        if (!$this->isCommissionEligible($settings)) {
            return;
        }

        $commission = $settings->commission_cap;

        // Update affiliate balance
        $affiliate->balance += $commission;
        $affiliate->save();

        // Update referral commission
        $referral->commissions = $commission;
        $referral->save();
    }

    /**
     * Check if commission should be processed based on settings
     */
    private function isCommissionEligible(AffiliateSetting $settings): bool
    {
        return $settings->commission_enabled === YesNo::Yes
            && $settings->payout_type === PayoutType::OnSignUp
            && $settings->commission_type === CommissionType::Fixed;
    }
}
