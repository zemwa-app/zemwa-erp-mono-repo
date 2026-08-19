<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Http\Controllers\SuperAdmin\FrontBaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Enums\Status;

class AffiliatePublicController extends FrontBaseController
{

    /**
     * Redirect referral to the original URL
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function redirectReferral(Request $request)
    {
        if (session()->has('referralCode')) {
            session()->forget('referralCode');
        }

        session()->put('referralCode', $request->route()->parameters['referral']);

        $this->referral = Affiliate::where('referral_code', session()->get('referralCode'))
            ->where('status', Status::Active)->exists();

        if ($this->referral) {
            return redirect(route('front.signup.index'))->with('flash-message', ['success', 'referral code applied successfully']);
        }
        else {
            return redirect(route('front.signup.index'))->with('flash-message', ['error', 'Invalid referral code.']);
        }
    }

}
