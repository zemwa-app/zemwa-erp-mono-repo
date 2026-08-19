<?php

namespace Modules\Zoom\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class ZoomApiClient
{
    private Client $http;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?: new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Server-to-Server OAuth: client_id/client_secret + account_id.
     */
    public function accessToken(): string
    {
        $clientId = (string) Config::get('zoom.api_key');
        $clientSecret = (string) Config::get('zoom.api_secret');
        $accountId = (string) Config::get('zoom.account_id');

        if ($clientId === '' || $clientSecret === '' || $accountId === '') {
            throw new RuntimeException('Zoom credentials are not configured (api_key, api_secret, account_id).');
        }

        $cacheKey = 'zoom:s2s_access_token:' . sha1($clientId . '|' . $accountId);

        return Cache::remember($cacheKey, now()->addMinutes(45), function () use ($cacheKey, $clientId, $clientSecret, $accountId) {
            try {
                $res = $this->http->post('https://zoom.us/oauth/token', [
                    'auth' => [$clientId, $clientSecret],
                    'query' => [
                        'grant_type' => 'account_credentials',
                        'account_id' => $accountId,
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
            } catch (GuzzleException $e) {
                throw new RuntimeException('Failed to obtain Zoom access token: ' . $e->getMessage(), 0, $e);
            }

            $data = json_decode((string) $res->getBody(), true);

            $token = (string) Arr::get($data, 'access_token', '');
            $expiresIn = (int) Arr::get($data, 'expires_in', 0);

            if ($token === '') {
                throw new RuntimeException('Zoom token response did not include access_token.');
            }

            if ($expiresIn > 120) {
                Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 60));
            }

            return $token;
        });
    }

    public function getMeeting(string $meetingId): array
    {
        return $this->requestJson('GET', "/meetings/{$meetingId}");
    }

    public function createMeeting(array $payload, string $userId = 'me'): array
    {
        return $this->requestJson('POST', "/users/{$userId}/meetings", [
            'json' => $payload,
        ]);
    }

    public function updateMeeting(string $meetingId, array $payload, ?string $occurrenceId = null): array
    {
        $query = [];

        if ($occurrenceId) {
            $query['occurrence_id'] = $occurrenceId;
        }

        $this->requestJson('PATCH', "/meetings/{$meetingId}", [
            'query' => $query ?: null,
            'json' => $payload,
        ], true);

        return [];
    }

    public function deleteMeeting(string $meetingId, ?string $occurrenceId = null): void
    {
        $query = [];

        if ($occurrenceId) {
            $query['occurrence_id'] = $occurrenceId;
        }

        $this->requestJson('DELETE', "/meetings/{$meetingId}", [
            'query' => $query ?: null,
        ], true);
    }

    public function endMeeting(string $meetingId): void
    {
        $this->requestJson('PUT', "/meetings/{$meetingId}/status", [
            'json' => ['action' => 'end'],
        ], true);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function requestJson(string $method, string $path, array $options = [], bool $allowNoContent = false): array
    {
        $token = $this->accessToken();

        $options = array_filter($options, fn ($v) => $v !== null);
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]);

        try {
            $res = $this->http->request($method, 'https://api.zoom.us/v2' . $path, $options);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Zoom API request failed: ' . $e->getMessage(), 0, $e);
        }

        $status = (int) $res->getStatusCode();

        if ($allowNoContent && $status === 204) {
            return [];
        }

        $body = (string) $res->getBody();
        if ($body === '') {
            return [];
        }

        $data = json_decode($body, true);

        if (! is_array($data)) {
            throw new RuntimeException('Zoom API returned non-JSON response.');
        }

        return $data;
    }
}
