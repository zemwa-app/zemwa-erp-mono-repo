<?php

namespace Modules\Affiliate\Http\Controllers;

use Modules\Affiliate\Entities\Payout;
use Modules\Affiliate\Entities\Referral;
use Modules\Affiliate\Entities\Affiliate;
use App\Http\Controllers\AccountBaseController;
use App\Models\GlobalSetting;

class DashBoardController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'affiliate::app.menu.dashboard';

        $this->middleware(function ($request, $next){
            abort_403(GlobalSetting::validateSuperAdmin());
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->managePermission = user()->permission('view_affiliate_dashboard');
        abort_403(!($this->managePermission == 'all'));

        $this->totalReferrals = Referral::count();
        $this->totalAffiliates = Affiliate::count();
        $totalPayout = Payout::where('status', 'paid')->sum('amount_requested');
        $this->totalPayouts = global_currency_format($totalPayout);
        $pendingPayout = Payout::where('status', 'pending')->sum('amount_requested');
        $this->pendingPayouts = global_currency_format($pendingPayout);

        $this->topAffiliates = Affiliate::selectRaw('affiliates.*, count(affiliates.user_id) as users, (SELECT COUNT(*) FROM affiliate_referrals WHERE affiliate_referrals.affiliate_id = affiliates.id) as total_referrals')
            ->withCount('user')
            ->with(['user', 'referral'])
            ->groupBy('affiliates.id')
            ->orderByDesc('total_referrals')
            ->limit(5)
            ->get();

        $this->latestCompanies = Referral::selectRaw('affiliate_referrals.*')
            ->with(['company', 'affiliate'])
            ->groupBy('company_id')
            ->latest()
            ->limit(5)
            ->get();

        return view('affiliate::dashboard.index', $this->data);
    }

}
