<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use App\Http\Controllers\AccountBaseController;
use App\Models\GlobalSetting;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Http\Requests\CreateSettingsRequest;

class AffiliateSettingController extends AccountBaseController
{

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'affiliate::app.menu.affiliateSettings';
        $this->activeSettingMenu = 'affiliate_settings';

        $this->middleware(function ($request, $next){
            abort_403(GlobalSetting::validateSuperAdmin());
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $this->viewPermission = user()->permission('manage_affiliate_settings');
        abort_403(!($this->viewPermission == 'all'));

        $this->settings = AffiliateSetting::first();
        return view('affiliate::affiliate-settings.index', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(CreateSettingsRequest $request, $id)
    {
        abort_403(user()->permission('manage_affiliate_settings') != 'all');

        if  ($request->payout_type == 'on signup' && $request->commission_type == 'percent') {
            return Reply::error(__('affiliate::messages.commissionTypeError'));
        }

        $settings = AffiliateSetting::findOrFail($id);
        $settings->commission_enabled = $request->commission_enabled;
        $settings->commission_type = $request->payout_type == 'after signup' ? $request->commission_type : 'fixed';
        $settings->payout_type = $request->payout_type;
        $settings->payout_time = ($request->payout_type == 'after signup') ? $request->payout_time : null;
        $settings->commission_cap = $request->commission_cap ? $request->commission_cap : 0;
        $settings->minimum_payout = $request->minimum_payout ? $request->minimum_payout : 0;
        $settings->save();

        return Reply::success(__('messages.updateSuccess'));
    }

}
