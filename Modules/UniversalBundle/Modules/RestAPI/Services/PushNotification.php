<?php

namespace Modules\RestAPI\Services;

/**
 * PushNotification Class
 * 
 * Handles sending push notifications to mobile devices using Firebase Cloud Messaging
 * Same implementation as backend project
 */
class PushNotification
{
    /**
     * Send a push notification to multiple devices
     *
     * @param string $message The notification message to display
     * @param array $data Additional data to send with the notification
     * @param \Illuminate\Support\Collection $devices Collection of device objects with registration_id
     * @return void
     */
    public static function sendMessage($message, $data, $devices)
    {
        // Extract unique registration IDs from the devices collection
        $deviceTokens = $devices->pluck('registration_id')
            ->filter() // Remove empty registration IDs
            ->unique() // Remove duplicates
            ->values() // Reset array keys
            ->toArray();

        // Skip if no valid device tokens
        if (empty($deviceTokens)) {
            return;
        }

        try {
            // Initialize Firebase with service account credentials
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(module_path('RestAPI', 'Config/firebase/clan-react-native-ff92d1db41e6.json'));

            // Get messaging service
            $messaging = $factory->createMessaging();

            // Create the notification message
            $baseMessage = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create('Clan', $message))
                ->withData($data);

            // Send message to all device tokens
            $report = $messaging->sendMulticast($baseMessage, $deviceTokens);
            
            // Log results
            \Log::info("Push notification sent: {$report->successes()->count()} successful, {$report->failures()->count()} failed");
            
        } catch (\Exception $e) {
            \Log::error('Error sending push notification: ' . $e->getMessage());
        }
    }
}
