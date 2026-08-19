<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\DealEmailTemplate\StoreRequest;
use App\Http\Requests\DealEmailTemplate\UpdateRequest;
use App\Models\Deal;
use App\Models\DealEmailTemplate;
use App\Services\DealEmailTemplateService;
use Illuminate\Http\Request;

class DealEmailTemplateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.deal.emailTemplates';
        $this->activeSettingMenu = 'lead_settings';

        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_deal_email_template') == 'none');

            return $next($request);
        });
    }

    public function index()
    {
        return redirect()->route('lead-settings.index', ['tab' => 'email-templates']);
    }

    public function create()
    {
        if (!request()->ajax()) {
            return redirect()->route('lead-settings.index', ['tab' => 'email-templates']);
        }

        return view('lead-settings.create-deal-email-template-modal');
    }

    public function store(StoreRequest $request)
    {
        $template = new DealEmailTemplate();
        $template->name = trim($request->name);
        $template->subject = trim($request->subject);
        $template->body = $request->body;
        $template->added_by = user()->id;
        $template->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        $this->template = DealEmailTemplate::findOrFail($id);

        if (!request()->ajax()) {
            return redirect()->route('lead-settings.index', ['tab' => 'email-templates']);
        }

        return view('lead-settings.edit-deal-email-template-modal', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $template = DealEmailTemplate::findOrFail($id);
        $template->name = trim($request->name);
        $template->subject = trim($request->subject);
        $template->body = $request->body;
        $template->save();

        return Reply::success(__('messages.templateUpdateSuccess'));
    }

    public function destroy($id)
    {
        DealEmailTemplate::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function fetchTemplate(Request $request)
    {
        $request->validate([
            'templateId' => 'required|integer|exists:deal_email_templates,id',
            'dealId' => 'required|integer|exists:deals,id',
        ]);

        $deal = Deal::findOrFail($request->dealId);
        $viewPermission = user()->permission('view_deals');
        $leadAgentId = ($deal->leadAgent != null) ? $deal->leadAgent->user->id : 0;

        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $deal->added_by == user()->id)
            || ($viewPermission == 'owned' && (($leadAgentId == user()->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
            || ($viewPermission == 'both' && ($deal->added_by == user()->id || $leadAgentId == user()->id || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
        ));

        $resolved = DealEmailTemplateService::fetchForDeal($request->templateId, $request->dealId);

        return Reply::dataOnly([
            'subject' => $resolved['subject'],
            'body' => $resolved['body'],
            'status' => 'success',
        ]);
    }
}
