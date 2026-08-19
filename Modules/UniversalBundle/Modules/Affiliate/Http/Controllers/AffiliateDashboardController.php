<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Helper\Reply;
use Modules\Affiliate\Entities\Payout;
use Modules\Affiliate\Entities\Referral;
use Modules\Affiliate\Entities\Affiliate;
use App\Http\Controllers\AccountBaseController;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\DataTables\PayoutsDataTable;
use Modules\Affiliate\DataTables\ReferralsDataTable;
use Modules\Affiliate\Http\Requests\UpdateAffiliate;

class AffiliateDashboardController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'affiliate::app.menu.affiliateDashboard';

        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('view_affiliate_dashboard') == 'all' || isAffiliate()));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_403(!isAffiliate());
        $this->settings = AffiliateSetting::first();
        $this->affiliate = Affiliate::with('user')->where('user_id', user()->id)->first();
        $affiliates = Affiliate::with('user')->where('user_id', user()->id)->get()->pluck('id');

        $this->referrals = Referral::whereIn('affiliate_id', $affiliates)->get();
        $totalPayout = Payout::where('status', 'paid')->whereIn('affiliate_id', $affiliates)->sum('amount_requested');
        $totalEarning = $totalPayout + $this->affiliate->balance;

        if (user()->is_superadmin) {
            $currencyId = global_setting()->currency_id;
        }
        else {
            $currencyId = user()->company->currency_id;
        }

        $this->currentBalance = currency_format($this->affiliate->balance, $currencyId);

        $this->payouts = currency_format($totalPayout, $currencyId);
        $this->totalEarnings = currency_format($totalEarning, $currencyId);

        $tab = request('tab');

        switch ($tab) {
        case 'payouts':
            return $this->payouts();
        case 'referrals':
            return $this->referrals();
        default:
            $this->view = 'affiliate::affiliate-dashboard.ajax.affiliates';
            break;
        }

        if (request()->ajax()) {
            $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'affiliates';

        return view('affiliate::affiliate-dashboard.index', $this->data);
    }

    public function payouts()
    {
        $dataTable = new PayoutsDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'payouts';

        $this->view = 'affiliate::affiliate-dashboard.ajax.payouts';

        return $dataTable->render('affiliate::affiliate-dashboard.index', $this->data);
    }

    public function referrals()
    {
        $dataTable = new ReferralsDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'referrals';

        $this->view = 'affiliate::affiliate-dashboard.ajax.referrals';

        return $dataTable->render('affiliate::affiliate-dashboard.index', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->affiliate = Affiliate::findOrFail($id);

        abort_403(!(user()->permission('edit_affiliates') == 'all'
            || (user()->id == $this->affiliate->user_id)
        ));
        return view('affiliate::affiliate.ajax.edit-slug', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAffiliate $request, $id)
    {
        $affiliate = Affiliate::findOrFail($id);
        abort_403(!(user()->permission('edit_affiliates') == 'all'
            || (user()->id == $affiliate->user_id)
        ));

        $affiliate->referral_code = $request->referral_code;
        $affiliate->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}
