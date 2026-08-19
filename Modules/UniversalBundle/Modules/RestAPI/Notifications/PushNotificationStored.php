<?php

namespace Modules\RestAPI\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PushNotificationStored extends Notification
{
    use Queueable;

    private array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via($_notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($_notifiable): array
    {
        return $this->payload;
    }

}
