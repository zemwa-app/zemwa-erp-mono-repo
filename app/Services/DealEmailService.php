<?php

namespace App\Services;

use App\Helper\Files;
use App\Mail\DealEmail;
use App\Models\Deal;
use App\Models\DealEmailAttachment;
use App\Models\DealEmailHistory;
use App\Traits\DealHistoryTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DealEmailService
{
    use DealHistoryTrait;

    public function send(Deal $deal, array $data): DealEmailHistory
    {
        $meta = [];

        if (!empty($data['cc'])) {
            $meta['cc'] = $data['cc'];
        }

        $history = DealEmailHistory::create([
            'company_id' => $deal->company_id,
            'deal_id' => $deal->id,
            'lead_id' => $deal->lead_id,
            'deal_email_template_id' => $data['deal_email_template_id'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'recipient_email' => $data['to'],
            'recipient_name' => $deal->contact->client_name ?? null,
            'sent_by' => user()->id,
            'status' => DealEmailHistory::STATUS_SENT,
            'meta' => $meta ?: null,
        ]);

        if (!empty($data['files'])) {
            foreach ($data['files'] as $fileData) {
                $hashname = Files::uploadLocalOrS3(
                    $fileData,
                    DealEmailAttachment::FILE_PATH . '/' . $history->id
                );

                DealEmailAttachment::create([
                    'deal_email_history_id' => $history->id,
                    'filename' => $fileData->getClientOriginalName(),
                    'hashname' => $hashname,
                    'size' => $fileData->getSize(),
                ]);
            }
        }

        $history->load('attachments');

        try {
            $mail = Mail::to($data['to']);

            if (!empty($data['cc'])) {
                $ccAddresses = array_filter(array_map('trim', explode(',', $data['cc'])));
                $mail->cc($ccAddresses);
            }

            $mailable = new DealEmail($history);

            if (smtp_setting()->mail_connection == 'database') {
                $mail->queue($mailable);
            } else {
                $mail->send($mailable);
            }
        } catch (\Throwable $e) {
            Log::error('Deal email send failed', [
                'deal_id' => $deal->id,
                'history_id' => $history->id,
                'recipient' => $data['to'],
                'error' => $e->getMessage(),
            ]);

            $history->update([
                'status' => DealEmailHistory::STATUS_FAILED,
                'meta' => array_merge($history->meta ?? [], ['error' => $e->getMessage()]),
            ]);

            return $history->fresh(['attachments', 'template', 'sentBy']);
        }

        self::createDealHistory($deal->id, 'email-sent');

        return $history->fresh(['attachments', 'template', 'sentBy']);
    }
}
