<?php

namespace App\Http\Controllers\Traits;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

trait PushNotificationTrait
{
    /**
     * Send a push notification to a single device via FCM.
     *
     * @param  string  $deviceToken
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data  Optional data payload
     * @return void
     */
    public function sendFcmMessage($deviceToken, $subject, $message, $data = [], $vendorNotification = 0, $userType = null)
    {
        $payloadData = $this->parseBookingData($data);
        $payloadData['vendorNotification'] = $vendorNotification;

        $settings = GeneralSetting::whereIn('meta_key', [
            'push_notification_status',
            'onesignal_app_id',
            'onesignal_rest_api_key',
            'onesignal_app_id_driver',
            'onesignal_rest_api_key_driver',
        ])->get()->pluck('meta_value', 'meta_key')->toArray();

        if (($settings['push_notification_status'] ?? null) === 'onesignal') {
            if ($userType === 'driver' || (int) $vendorNotification === 1) {
                $appId = $settings['onesignal_app_id_driver'] ?? null;
                $appApiKey = $settings['onesignal_rest_api_key_driver'] ?? null;
            } else {
                $appId = $settings['onesignal_app_id'] ?? null;
                $appApiKey = $settings['onesignal_rest_api_key'] ?? null;
            }

            if (blank($appId) || blank($appApiKey) || blank($deviceToken)) {
                Log::warning('OneSignal notification skipped because configuration is incomplete.');

                return response()->json([
                    'success' => false,
                    'message' => 'Push notification configuration is incomplete.',
                ], 503);
            }

            $payload = [
                'app_id' => $appId,
                'include_subscription_ids' => [$deviceToken],
                'target_channel' => 'push',
                'contents' => ['en' => $message],
                'headings' => ['en' => $subject],
                'data' => $payloadData,
            ];

            try {
                $response = Http::acceptJson()
                    ->withHeaders(['Authorization' => 'Key '.$appApiKey])
                    ->timeout(15)
                    ->post('https://api.onesignal.com/notifications', $payload);

                Log::info('OneSignal notification attempt', [
                    'subscription_hash' => hash('sha256', $deviceToken),
                    'http_code' => $response->status(),
                ]);

                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Notification sent to user.',
                    ]);
                }

                Log::warning('OneSignal rejected a notification request', [
                    'http_code' => $response->status(),
                    'response' => $response->json(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification.',
                ], 502);
            } catch (Throwable $exception) {
                Log::error('OneSignal notification request failed', [
                    'exception' => $exception->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification.',
                ], 502);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Push notifications are not configured for OneSignal.',
        ], 503);
    }

    /**
     * Send a push notification to multiple devices via FCM.
     *
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data  Optional data payload
     * @return void
     */
    public function sendPushNotificationsToDevices(array $deviceTokens, $title, $body, $data = [])
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = env('FCM_SERVER_KEY');

        $payload = [
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        $headers = [
            'Authorization' => 'key='.$serverKey,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->post($url, $payload);

        if ($response->failed()) {
            Log::error('Failed to send push notifications', ['response' => $response->body()]);
        }
    }

    /**
     * Send a push notification to all devices subscribed to a particular topic via FCM.
     *
     * @param  string  $topic
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data  Optional data payload
     * @return void
     */
    public function sendPushNotificationToTopic($topic, $title, $body, $data = [])
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = env('FCM_SERVER_KEY');

        $payload = [
            'to' => '/topics/'.$topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        $headers = [
            'Authorization' => 'key='.$serverKey,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->post($url, $payload);

        if ($response->failed()) {
            Log::error('Failed to send push notification to topic', ['response' => $response->body()]);
        }
    }

    private function parseBookingData($data)
    {

        $checkIn = $this->extractValue($data, 'check_in');

        if ($checkIn !== null) {
            $bookingStatus = $this->extractValue($data, 'status') ?? 'Unknown';

            return [
                'status' => $bookingStatus,
                'route' => 'booking',
            ];
        }
        $guestRating = $this->extractValue($data, 'guest_rating');
        $hostRating = $this->extractValue($data, 'host_rating');

        // If either guest_rating or host_rating exists, set route to 'review'
        if ($guestRating !== null || $hostRating !== null) {
            return [
                'route' => 'review',
            ];
        }

        return [

            'route' => 'none',
        ];
    }

    private function extractValue($data, $key)
    {

        if (is_array($data) || is_object($data)) {

            if (isset($data[$key])) {
                return $data[$key];
            }

            foreach ($data as $item) {
                $result = $this->extractValue($item, $key);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }
}
