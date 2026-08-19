<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use Modules\Affiliate\Entities\Payout;
use Modules\Affiliate\Enums\PaymentStatus;
use App\Http\Controllers\AccountBaseController;
use Modules\Affiliate\DataTables\PayoutsDataTable;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Http\Requests\StorePayout;

class PayoutController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'affiliate::app.menu.payouts';

        $this->middleware(function ($request, $next){
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PayoutsDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_payouts');
        abort_403((!in_array($this->viewPermission, ['all', 'owned'])));
        $this->users = Affiliate::all();

        return $dataTable->render('affiliate::payout.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->viewPermission = user()->permission('add_payouts');
        abort_403(!($this->viewPermission == 'all'));

        $this->pageTitle = __('affiliate::app.createWithdrawal');
        $this->view = 'affiliate::payout.ajax.create';
        $this->affiliateSetting = AffiliateSetting::first();
        $this->users = Affiliate::all();

        if ($this->users->isEmpty()) {
            return redirect(route('payout.index'));
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('affiliate::affiliate.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePayout $request)
    {
        $affiliate = Affiliate::findOrFail($request->affiliate_id);

        $alreadyRequested = Payout::where('affiliate_id', $request->affiliate_id)->where('status', PaymentStatus::Pending)->exists();

        if ($alreadyRequested) {
            return Reply::error(__('affiliate::messages.alreadyRequested'));
        }

        $payout = new Payout();
        $payout->affiliate_id = $request->affiliate_id;
        $payout->balance = $affiliate->balance;
        $payout->amount_requested = $request->amount;
        $payout->payment_method = $request->payment_method;
        $payout->other_payment_method = $request->other_payment_method;
        $payout->note = $request->note;
        $payout->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $this->payout = Payout::with('affiliate')->findOrFail($id);
        $this->viewPermission = user()->permission('view_payouts');

        abort_403(!($this->viewPermission == 'all'
            || (user()->id == $this->payout->affiliate->user_id)
        ));

        $this->pageTitle = __('affiliate::app.payoutDetail');
        $this->view = 'affiliate::payout.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('affiliate::affiliate.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->payout = Payout::findOrFail($id);
        $this->editPermission = user()->permission('edit_payouts');
        abort_403(!($this->editPermission == 'all'
            || (user()->id == $this->payout->affiliate->user_id)
        ));

        $this->pageTitle = __('affiliate::app.editWithdrawal');
        $this->view = 'affiliate::payout.ajax.edit';
        $this->affiliateSetting = AffiliateSetting::first();
        $this->users = Affiliate::all();

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('affiliate::affiliate.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePayout $request, $id)
    {
        $affiliate = Affiliate::findOrFail($request->affiliate_id);
        $payout = Payout::findOrFail($id);
        $payout->affiliate_id = $request->affiliate_id;
        $payout->balance = $affiliate->balance;
        $payout->amount_requested = $request->amount;
        $payout->payment_method = $request->payment_method;
        $payout->other_payment_method = $request->other_payment_method;
        $payout->note = $request->note;
        $payout->save();

        $redirectUrl = route('payout.show', $payout->id);

        return Reply::redirect($redirectUrl, __('messages.recordUpdated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $payout = Payout::findOrFail($id);
        $this->deletePermission = user()->permission('delete_payouts');
        abort_403(!($this->deletePermission == 'all'
            || (     user()->id == $payout->affiliate->user_id)
        ));

        $payout->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function changeStatus(Request $request)
    {
        $this->viewPermission = user()->permission('manage_payout_status');
        abort_403(!($this->viewPermission == 'all'));

        $payout = Payout::findOrFail($request->id);

        if ($request->status) {
            if ($request->status == PaymentStatus::Paid->value) {
                if ($payout->affiliate->balance >= $payout->amount_requested) {
                    $payout->affiliate->balance -= $payout->amount_requested;
                    $payout->affiliate->save();
                    Payout::where('id', $request->id)->update(['paid_at' => now(), 'transaction_id' => $request->transaction_id, 'memo' => $request->memo]);
                }
                else {
                    return Reply::error(__('affiliate::messages.insufficientBalance'));
                }
            }

            $payout->update(['status' => $request->status]);
        }
        else {
            return Reply::error(__('messages.selectAction'));
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function paidConfirmation($id)
    {
        $this->viewPermission = user()->permission('manage_payout_status');
        abort_403(!($this->viewPermission == 'all'));

        $this->payout = Payout::findOrFail($id);
        $this->pageTitle = __('affiliate::app.confirmPaid');

        return view('affiliate::payout.ajax.confirm-paid', $this->data);
    }

}
