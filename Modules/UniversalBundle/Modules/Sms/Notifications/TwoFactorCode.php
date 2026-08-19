<?php

namespace Modules\Sms\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use Modules\Sms\Entities\SmsNotificationSetting;
use Modules\Sms\Http\Traits\WhatsappMessageTrait;
use NotificationChannels\Telegram\TelegramMessage;
use Modules\Sms\Entities\SmsTemplateId;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class TwoFactorCode extends Notification implements ShouldQueue
{

    use Queueable, WhatsappMessageTrait;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $timeLog;

    private $msg_flow_id;

    private $message;

    private $smsSetting;

    public function __construct()
    {
        $this->smsSetting = SmsNotificationSetting::where('slug', 'two-factor-code')->first();
        $this->msg_flow_id = SmsTemplateId::where('sms_setting_slug', 'new-task')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->smsSetting && $this->smsSetting->send_sms != 'yes') {
            return [];
        }
        $code = $notifiable->userAuth->two_factor_code;

        $this->message = __('email.twoFactor.line1') . $code . "\n" . __('email.twoFactor.line2') . "\n" . __('email.twoFactor.line3');

        $via = [];


        if (! is_null($notifiable->mobile) && ! is_null($notifiable->country_phonecode)) {
            if (sms_setting()->status) {
                array_push($via, TwilioChannel::class);
            }

            if (sms_setting()->nexmo_status) {


                array_push($via, 'vonage');
            }

            if (sms_setting()->msg91_status) {
                array_push($via, 'msg91');
            }
        }

        if (sms_setting()->telegram_status && $notifiable->telegram_user_id) {
            array_push($via, 'telegram');
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $code = $notifiable->userAuth->two_factor_code;
        if (sms_setting()->whatsapp_status && $notifiable->whatapp_from_number) {
            $this->toWhatsapp(
                $this->smsSetting->slug,
                $notifiable,
                __($this->smsSetting->slug->translationString(), ['code' => $code]),
                ['1' => $code]
            );
        }

        if (sms_setting()->status) {
            return (new TwilioSmsMessage)
                ->content($this->message);
        }
    }

    //phpcs:ignore
    public function toVonage($notifiable)
    {
        if (sms_setting()->nexmo_status) {
            return (new VonageMessage)
                ->content($this->message)->unicode();
        }
    }

    public function toMsg91($notifiable)
    {

        $code = $notifiable->userAuth->two_factor_code;

        if ($this->smsSetting->msg91_flow_id && sms_setting()->msg91_status) {
            return (new \Craftsys\Notifications\Messages\Msg91SMS)
                ->flow($this->smsSetting->msg91_flow_id)
                ->variable('two_factor_code', $code);
        }
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            // Optional recipient user id.
            ->to($notifiable->telegram_user_id)
            // Markdown supported.
            ->content($this->message);
    }

}
