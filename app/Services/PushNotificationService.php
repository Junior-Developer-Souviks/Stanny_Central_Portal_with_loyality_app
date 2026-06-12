<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    public function send(string $fcmToken, string $title, string $body, array $data = [], string $deviceType = 'android'): void
    {
        $payload = [
            'to'   => $fcmToken,
            'data' => $data,  // always include data block for both
        ];

        if ($deviceType === 'ios') {
            // iOS needs 'notification' block + content_available
            $payload['notification'] = [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'badge' => 1,
            ];
            $payload['content_available'] = true;
            $payload['apns-priority']     = 10;

        } else {
            // Android
            $payload['notification'] = [
                'title'        => $title,
                'body'         => $body,
                'sound'        => 'default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];
        }

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.server_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
    }
}