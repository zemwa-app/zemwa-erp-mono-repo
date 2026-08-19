<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Models\User;
use App\Helper\Reply;
use App\Models\Company;
use App\Models\GlobalSetting;
use Modules\Affiliate\Entities\Referral;
use Modules\Affiliate\Entities\Affiliate;
use App\Http\Controllers\AccountBaseController;
use Modules\Affiliate\DataTables\ReferralsDataTable;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Enums\CommissionType;
use Modules\Affiliate\Enums\PayoutType;
use Modules\Affiliate\Enums\YesNo;
use Modules\Affiliate\Http\Requests\CreateReferralsRequest;

class ReferralsController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'affiliate::app.menu.referrals';

        $this->middleware(function ($request, $next){
            abort_403(GlobalSetting::validateSuperAdmin());
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ReferralsDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_referrals');
        abort_403(!($this->viewPermission == 'all'));

        $this->companies = Company::active()->get();
        $this->affiliates = Affiliate::whereHas('user')->active()->get();

        return $dataTable->render('affiliate::referrals.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->viewPermission = user()->permission('add_referrals');
        abort_403(!($this->viewPermission == 'all'));

        $this->pageTitle = __('affiliate::app.createReferralsCommissions');
        $this->view = 'affiliate::referrals.ajax.create';

        $this->affiliates = Affiliate::whereHas('user')->active()->get();
        $this->companies = Company::whereNotIn('id', Referral::pluck('company_id'))->active()->get();

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('affiliate::referrals.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateReferralsRequest $request)
    {
        $this->viewPermission = user()->permission('add_referrals');
        abort_403(!($this->viewPermission == 'all'));

        $referral = new Referral();
        $referral->company_id = $request->company_id;
        $referral->affiliate_id = $request->affiliate_id;
        $referral->ip = request()->getClientIp();
        $referral->user_agent = request()->userAgent();
        $referral->save();

        $affiliate = Affiliate::find($request->affiliate_id);
        $affiliateSettings = AffiliateSetting::first();

        if ($affiliateSettings->commission_enabled == YesNo::Yes &&
            $affiliateSettings->payout_type == PayoutType::OnSignUp &&
            $affiliateSettings->commission_type == CommissionType::Fixed) {
                // Add commission to affiliate balance
                $affiliate->balance += $affiliateSettings->commission_cap;
                $affiliate->save();

                // Add commission to referral
                $referral->commissions = $affiliateSettings->commission_cap;
                $referral->save();
        }

        return Reply::redirect(route('referral.index'));
    }

    /**
     * Get affiliate except selected company owner.
     */
    public function getAffiliates($companyId)
    {
        $users = User::where('company_id', $companyId)->pluck('id');
        $affiliates = Affiliate::with('user')->whereNotIn('user_id', $users)->active()->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $affiliates]);
    }

}
