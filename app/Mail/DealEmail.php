<?php

namespace App\Mail;

use App\Models\DealEmailHistory;
use App\Models\GlobalSetting;
use App\Models\SmtpSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class DealEmail extends Mailable
{
    use Queueable, SerializesModels;

    public DealEmailHistory $emailHistory;

    public function __construct(DealEmailHistory $emailHistory)
    {
        $this->emailHistory = $emailHistory;
    }

    public function build()
    {
        $smtpSetting = SmtpSetting::first();
        $globalSetting = GlobalSetting::first();

        if ($smtpSetting) {
            $this->applyMailConfig($smtpSetting);
        }

        if ($globalSetting) {
            Config::set('app.logo', $globalSetting->masked_logo_url);
        }

        $fromEmail = $smtpSetting->mail_from_email ?? config('mail.from.address');
        $fromName = $smtpSetting->mail_from_name ?? config('mail.from.name');

        if ($smtpSetting) {
            Config::set('app.name', $fromName);
        }

        $mail = $this->from($fromEmail, $fromName)
            ->subject($this->emailHistory->subject)
            ->markdown('mail.email', [
                'content' => $this->emailHistory->body,
                'notifiableName' => $this->emailHistory->recipient_name,
            ]);

        foreach ($this->emailHistory->attachments as $attachment) {
            $contents = $attachment->getFileContents();

            if ($contents !== null) {
                $mail->attachData($contents, $attachment->filename);
            }
        }

        return $mail;
    }

    private function applyMailConfig(SmtpSetting $smtpSetting): void
    {
        if (!in_array(app()->environment(), ['demo', 'development'])) {
            $driver = ($smtpSetting->mail_driver != 'mail') ? $smtpSetting->mail_driver : 'sendmail';

            Config::set('mail.default', $driver);
            Config::set('mail.mailers.smtp.host', $smtpSetting->mail_host);
            Config::set('mail.mailers.smtp.port', $smtpSetting->mail_port);
            Config::set('mail.mailers.smtp.username', $smtpSetting->mail_username);
            Config::set('mail.mailers.smtp.password', $smtpSetting->mail_password);
            Config::set('mail.mailers.smtp.encryption', $smtpSetting->mail_encryption);
            Config::set('mail.verified', (bool) $smtpSetting->email_verified);
        }

        Config::set('mail.from.name', $smtpSetting->mail_from_name);
        Config::set('mail.from.address', $smtpSetting->mail_from_email);
    }
}
