<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Deal\SendEmailRequest;
use App\Models\Deal;
use App\Models\DealEmailAttachment;
use App\Models\DealEmailHistory;
use App\Models\DealEmailTemplate;
use App\Services\DealEmailService;

class DealEmailController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-people';
        $this->pageTitle = 'modules.deal.sendEmail';
    }

    private function authorizeDealAccess(Deal $deal): void
    {
        $viewPermission = user()->permission('view_deals');
        $leadAgentId = ($deal->leadAgent != null) ? $deal->leadAgent->user->id : 0;

        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $deal->added_by == user()->id)
            || ($viewPermission == 'owned' && (($leadAgentId == user()->id) || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
            || ($viewPermission == 'both' && ($deal->added_by == user()->id || $leadAgentId == user()->id || (!is_null($deal->deal_watcher) && user()->id == $deal->deal_watcher)))
        ));
    }

    public function create($dealId)
    {
        abort_403(user()->permission('send_deal_email') == 'none');

        $this->deal = Deal::with(['contact', 'leadAgent.user'])->findOrFail($dealId);
        $this->authorizeDealAccess($this->deal);

        $this->templates = DealEmailTemplate::orderBy('name')->get();
        $this->pageTitle = __('modules.deal.sendEmail');
        $this->view = 'leads.ajax.send-email';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return redirect()->route('deals.show', $dealId);
    }

    public function store(SendEmailRequest $request, $dealId)
    {
        abort_403(user()->permission('send_deal_email') == 'none');

        $deal = Deal::with(['contact', 'leadAgent.user'])->findOrFail($dealId);
        $this->authorizeDealAccess($deal);

        $service = new DealEmailService();
        $history = $service->send($deal, [
            'to' => $request->to,
            'cc' => $request->cc,
            'subject' => $request->subject,
            'body' => $request->body,
            'deal_email_template_id' => $request->deal_email_template_id,
            'files' => $request->file('file', []),
        ]);

        if ($history->status === DealEmailHistory::STATUS_FAILED) {
            return Reply::error(__('modules.deal.emailSendFailed'));
        }

        return Reply::success(__('modules.deal.emailSentSuccess'));
    }

    public function show($id)
    {
        abort_403(user()->permission('view_deal_email') == 'none');

        $this->emailHistory = DealEmailHistory::with(['attachments', 'template', 'sentBy', 'deal'])
            ->findOrFail($id);

        $this->authorizeDealAccess($this->emailHistory->deal);
        $this->pageTitle = $this->emailHistory->subject;
        $this->view = 'leads.ajax.email-show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return redirect(route('deals.show', $this->emailHistory->deal_id) . '?tab=emails');
    }

    public function downloadAttachment($historyId, $attachmentId)
    {
        abort_403(user()->permission('view_deal_email') == 'none');

        $history = DealEmailHistory::with('deal')->findOrFail($historyId);
        $this->authorizeDealAccess($history->deal);

        $attachment = DealEmailAttachment::where('deal_email_history_id', $historyId)
            ->findOrFail($attachmentId);

        return download_local_s3($attachment, $attachment->getStoragePath());
    }
}
