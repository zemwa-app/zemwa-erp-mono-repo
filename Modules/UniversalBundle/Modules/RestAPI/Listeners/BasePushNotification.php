<?php

namespace Modules\RestAPI\Listeners;

use Edujugon\PushNotification\PushNotification;
use Modules\RestAPI\Entities\Device;
use Modules\RestAPI\Entities\FirebaseNotification;
use Modules\RestAPI\Entities\RestAPISetting;
use Modules\RestAPI\Services\FirebaseNotificationService;

class BasePushNotification
{

    protected static $sentNotificationKeys = [];
    protected $currentMessage;
    protected $apnPush;
    protected $fcmPush;
    protected $message = [];
    protected $firebaseService;

    public function __construct()
    {
        // No longer need legacy push notification setup
        $this->firebaseService = app(FirebaseNotificationService::class);
    }

    public function devices($user, $deviceType = null)
    {
        if (!$user) {
            return collect();
        }

        $devices = Device::query()
            ->where('user_id', $user->id)
            ->where('type', $deviceType)
            ->whereNotNull('registration_id')
            ->pluck('registration_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (!$userRestAPI || !$userRestAPI->devices) {
            return collect();
        }

        // Return devices, optionally filtered by type (android/ios)
        $devices = $userRestAPI->devices->where('registration_id', '!=', null);

        if ($deviceType) {
            $devices = $devices->where('type', strtolower($deviceType));
        }

        return $devices;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        // Keep for backward compatibility (not required with Option B)
        $this->currentMessage = $message;
    }

    public function sendNotification($user): void
    {
        if (!$this->shouldDispatchNotification($user)) {
            return;
        }

        $this->storeNotification($user);

        if ($this->firebaseService->isConfigured()) {
            $this->sendFirebaseNotification($user);

            return;
        }

        $set = $this->setKey();

        // If keys exist then send push notification
        if ($set) {
            $this->apnPush->setDevicesToken($this->devices($user, 'ios'))->send();
            $this->fcmPush->setDevicesToken($this->devices($user, 'android'))->send();
        }

    }

    protected function shouldDispatchNotification($user): bool
    {
        if (!$user) {
            return false;
        }

        $payload = $this->message['fcm']['data'] ?? $this->message['apn']['notification'] ?? [];

        if (empty($payload) || !is_array($payload)) {
            return false;
        }

        $fingerprint = md5(json_encode([
            'user_id' => (int)$user->id,
            'type' => $payload['type'] ?? null,
            'id' => $payload['id'] ?? null,
            'title' => $payload['title'] ?? null,
            'body' => $payload['body'] ?? null,
        ]));

        if (isset(self::$sentNotificationKeys[$fingerprint])) {
            return false;
        }

        self::$sentNotificationKeys[$fingerprint] = true;

        return true;
    }

    protected function storeNotification($user): void
    {
        if (!$user) {
            return;
        }

        $payload = $this->message['fcm']['data'] ?? $this->message['apn']['notification'] ?? [];

        if (empty($payload) || !is_array($payload)) {
            return;
        }

        try {
            FirebaseNotification::create([
                'user_id' => $user->id,
                'title' => $payload['title'] ?? null,
                'body' => $payload['body'] ?? null,
                'type' => $payload['type'] ?? null,
                'data' => $payload,
            ]);
        } catch (\Throwable $e) {
            // Keep push delivery independent from DB-notification persistence errors.
            report($e);
        }
    }

    // Function to set the FCM_KEY key before sending message.
    public function setKey()
    {
        $setting = RestAPISetting::first();

        $fcm_key = !is_null($setting->fcm_key) ? $setting->fcm_key : config('pushnotification.fcm.apiKey');

        // If key does not exist return false
        if (is_null($fcm_key)) {
            return false;
        }

        $this->apnPush->setApiKey($fcm_key);
        $this->fcmPush->setApiKey($fcm_key);

        return true;
    }

    protected function sendFirebaseNotification($user): void
    {
        $iosTokens = $this->devices($user, 'ios');
        $androidTokens = $this->devices($user, 'android');

        $fcmData = $this->message['fcm']['data'] ?? [];
        $apnData = $this->message['apn']['notification'] ?? [];
        $title = $apnData['title'] ?? ($fcmData['title'] ?? '');
        $body = $apnData['body'] ?? ($fcmData['body'] ?? '');


        if(!empty($iosTokens)) {

            $this->firebaseService->sendToTokens($iosTokens, (string)$title, (string)$body, $fcmData);
        }

        if(!empty($androidTokens)) {
            $this->firebaseService->sendToTokens($androidTokens, (string)$title, (string)$body, $fcmData);
        }
    }

    public function getUserRole($user)
    {
        try {
            if ($user->hasRole('admin')) {
                return 'admin';
            }

            if ($user->hasRole('employee')) {
                return 'employee';
            }
        } catch (\Exception $e) {
            return 'employee';
        }

    }

    public function pushNotificationArray($notificationData): array
    {
        return [
            'apn' => [
                'notification' => $notificationData,
            ],
            'fcm' => [
                'data' => $notificationData,
            ],
        ];
    }

}
