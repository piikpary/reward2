<?php

use Google\Client;

if (!function_exists('generateQrString')) {
    function generateQrString(string $name, string $phone): string
    {
        $dataPayload = create_tlv('59', $name) . create_tlv('99', $phone);

        $crcValue = crc16_ccitt_false($dataPayload);

        $crcHex = sprintf('%04X', $crcValue);

        $crcTlv = create_tlv('63', $crcHex);

        return $dataPayload . $crcTlv;
    }
}

if (!function_exists('create_tlv')) {
    function create_tlv(string $tag, string $value): string
    {
        $length = sprintf('%02d', strlen($value));

        return $tag . $length . $value;
    }
}

if (!function_exists('crc16_ccitt_false')) {
    function crc16_ccitt_false(string $data): int
    {
        $crc = 0xFFFF;
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= ord($data[$i]) << 8;

            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }

        return $crc & 0xFFFF;
    }
}

if (!function_exists('sendFcmNotification')) {
    function sendFcmNotification(string $deviceToken, string $title, string $body, array $data = []): array
    {
        $projectId = config('services.fcm.project_id');
        $keyFilePath = config('services.fcm.service_account_path');

        if (!$projectId || !$keyFilePath) {
            throw new Exception('FCM Project ID or Service Account Path is not configured.');
        }

        if (!file_exists($keyFilePath)) {
            throw new Exception("Service account key file not found at: {$keyFilePath}");
        }

        $client = new Client();
        $client->setAuthConfig($keyFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (!isset($token['access_token'])) {
            throw new Exception('Failed to fetch access token. Response: ' . json_encode($token));
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $badgeCount = max(0, (int) ($data['badge'] ?? 1));

        $payloadData = collect(array_merge($data, [
            'badge' => (string) $badgeCount,
            'title' => (string) $title,
            'body' => (string) $body,
        ]))
            ->map(fn ($value) => (string) $value)
            ->toArray();

        $message = [
            'message' => [
                'token' => $deviceToken,
                
                // ✅ ONLY data - no notification, no android block
                'data' => $payloadData,
                
                // ✅ For iOS badge
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'badge' => $badgeCount,
                            'sound' => 'default',
                        ],
                    ],
                ],
            ],
        ];

        $headers = [
            'Authorization: Bearer ' . $token['access_token'],
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($result, true);

        if ($httpCode !== 200) {
            throw new Exception('FCM request failed. Status: ' . $httpCode . ' Response: ' . $result);
        }

        return $response ?? [];
    }
}