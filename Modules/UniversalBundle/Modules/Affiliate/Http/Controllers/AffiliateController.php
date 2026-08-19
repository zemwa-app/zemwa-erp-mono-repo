<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Modules\Affiliate\Entities\Payout;
use Modules\Affiliate\Entities\Referral;
use Modules\Affiliate\Entities\Affiliate;
use App\Http\Controllers\AccountBaseController;
use App\Models\GlobalSetting;
use Modules\Affiliate\DataTables\AffiliatesDataTable;
use Modules\Affiliate\DataTables\PayoutsDataTable;
use Modules\Affiliate\DataTables\ReferralsDataTable;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Http\Requests\StoreAffiliates;

class AffiliateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'affiliate::app.menu.affiliates';

        $this->middleware(function ($request, $next){
            abort_403(GlobalSetting::validateSuperAdmin());
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(AffiliatesDataTable $dataTable)
    {
        $this->managePermission = user()->permission('view_affiliates');
        abort_403(!($this->managePermission == 'all'));

        return $dataTable->render('affiliate::affiliate.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->managePermission = user()->permission('add_affiliates');
        abort_403(!($this->managePermission == 'all'));

        $this->pageTitle = __('affiliate::app.createAffiliate');
        $this->view = 'affiliate::affiliate.ajax.create';
        $this->users = User::whereNotIn('id', Affiliate::pluck('user_id'))->where('is_superadmin', 0)->withoutGlobalScope(ActiveScope::class)->get();

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('affiliate::affiliate.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAffiliates $request)
    {
        abort_403(user()->permission('add_affiliates') != 'all');

        $affiliate = new Affiliate();
        $affiliate->user_id = $request->user_id;
        $affiliate->referral_code = $this->generateUniqueReferralCode();
        $affiliate->save();

        \session()->forget('isAffiliate');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('affiliate.index');
        }

        return Reply::redirect($redirectUrl, __('messages.recordSaved'));
    }

    /**
     * Generate unique referral code.
     */
    public function generateUniqueReferralCode()
    {
        $referralCode = str_random(8);
        $affiliate = Affiliate::where('referral_code', $referralCode)->first();

        if ($affiliate) {
            return $this->generateUniqueReferralCode();
        }

        return $referralCode;
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        abort_403(user()->permission('view_affiliates') != 'all');

        $this->settings = AffiliateSetting::first();
        $this->affiliate = Affiliate::with('user')->findOrFail($id);

        $this->referrals = Referral::where('affiliate_id', $id)->get();
        $totalPayout = Payout::where('status', 'paid')->where('affiliate_id', $id)->sum('amount_requested');
        $totalEarning = $totalPayout + $this->affiliate->balance;

        if (user()->is_superadmin) {
            $currencyId = global_setting()->currency_id;
        }
        else {
            $currencyId = user()->company->currency_id;
        }

        $this->currentBalance = global_currency_format($this->affiliate->balance, $currencyId);
        $this->payouts = global_currency_format($totalPayout, $currencyId);
        $this->totalEarnings = global_currency_format($totalEarning, $currencyId);

        $tab = request('tab');

        switch ($tab) {
        case 'payouts':
            return $this->payouts();
        case 'referrals':
            return $this->referrals();
        default:
            $this->view = 'affiliate::affiliate.ajax.affiliates';
            break;
        }

        if (request()->ajax()) {
            $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'affiliates';

        return view('affiliate::affiliate.show', $this->data);
    }

    public function payouts()
    {
        abort_403(user()->permission('view_payouts') != 'all');

        $dataTable = new PayoutsDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'payouts';

        $this->view = 'affiliate::affiliate.ajax.payouts';

        return $dataTable->render('affiliate::affiliate.show', $this->data);
    }

    public function referrals()
    {
        abort_403(user()->permission('view_referrals') != 'all');

        $dataTable = new ReferralsDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'referrals';

        $this->view = 'affiliate::affiliate.ajax.referrals';

        return $dataTable->render('affiliate::affiliate.show', $this->data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_403(user()->permission('delete_affiliates') != 'all');

        $affiliate = Affiliate::find($id);
        $affiliate->delete();
        session()->forget('isAffiliate');

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function changeStatus(Request $request)
    {
        abort_403(user()->permission('manage_affiliate_status') != 'all');

        if ($request->status) {
            Affiliate::where('id', $request->id)->update(['status' => $request->status]);
        }
        else {
            return Reply::error(__('messages.selectAction'));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

}
