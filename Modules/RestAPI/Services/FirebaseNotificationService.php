<?php

namespace Modules\RestAPI\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\RestAPI\Entities\RestAPISetting;

class FirebaseNotificationService
{
    protected ?array $credentials = null;

    protected ?string $projectId = null;

    protected ?string $accessToken = null;

    public function __construct()
    {
        $settings = RestAPISetting::first();
        $file = $settings?->fcm_service_account_file ?: null;

        if ($file) {
            $disk = Storage::disk(config('filesystems.default'));
            $path = 'firebase/' . $file;

            if ($disk->exists($path)) {
                $json = $disk->get($path);
                $this->credentials = json_decode($json, true);
                $this->projectId = $this->credentials['project_id'] ?? null;
            }
        }
    }

    public function isConfigured(): bool
    {
        $settings = RestAPISetting::first();

        if (empty($settings?->firebase_enabled)) {
            Log::info('RestAPI FCM: firebase_enabled is off.');

            return false;
        }

        $configured = !empty($this->projectId)
            && !empty($this->credentials['client_email'])
            && !empty($this->credentials['private_key']);

        if (!$configured) {
            Log::warning('RestAPI FCM: Missing project_id/client_email/private_key in uploaded JSON.');
        }

        return $configured;
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // Check incoming tokens for null/empty values before filtering
        if (empty($tokens) || !is_array($tokens)) {
            $tokens = [];
        } else {
            // Remove entries that are null, empty, or not scalar, then deduplicate and reindex
            $tokens = array_values(
                array_unique(
                    array_filter(
                        $tokens,
                        function($value) {
                            return is_scalar($value) && trim((string)$value) !== '';
                        }
                    )
                )
            );
        }
        if (empty($tokens)) {
            Log::info('RestAPI FCM: No valid device tokens found.');

            return false;
        }

        $token = $this->getAccessToken();

        if (!$token) {
            Log::warning('RestAPI FCM: Access token is null.');

            return false;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
        $success = false;
        $dataPayload = $this->stringifyData($data);

        foreach ($tokens as $deviceToken) {
            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $dataPayload,
                    'android' => [
                        'priority' => 'high',
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($url, $payload);

            if ($response->successful()) {
                $success = true;
            }
        }

        return $success;
    }

    protected function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        if (!empty($this->accessToken)) {
            return $this->accessToken;
        }

        $clientEmail = $this->credentials['client_email'] ?? null;
        $privateKey = $this->credentials['private_key'] ?? null;

        if (!$clientEmail || !$privateKey) {
            return null;
        }

        $privateKey = str_replace('\\n', "\n", $privateKey);
        $jwt = $this->createJwt($clientEmail, $privateKey);

        if (!$jwt) {
            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            Log::warning('RestAPI FCM: OAuth2 token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $this->accessToken = $response->json('access_token');

        return $this->accessToken;
    }

    protected function stringifyData(array $data): array
    {
        $formatted = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $formatted[(string)$key] = (string)$value;

                continue;
            }

            $formatted[(string)$key] = json_encode($value);
        }

        return $formatted;
    }

    protected function createJwt(string $clientEmail, string $privateKey): ?string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now = time();
        $payload = [
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $headerEnc = $this->base64UrlEncode(json_encode($header));
        $payloadEnc = $this->base64UrlEncode(json_encode($payload));
        $signatureInput = $headerEnc . '.' . $payloadEnc;

        $signature = '';
        $ok = openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$ok) {
            return null;
        }

        return $signatureInput . '.' . $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

}
