<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TikTokEventsService
{
    private ?string $pixelId;
    private ?string $accessToken;
    private string $endpoint;
    private ?string $testEventCode;

    public function __construct()
    {
        $this->pixelId = config('services.tiktok.pixel_id');
        $this->accessToken = config('services.tiktok.access_token');
        $this->endpoint = config('services.tiktok.endpoint', 'https://business-api.tiktok.com/open_api/v1.3/event/track/');
        $this->testEventCode = config('services.tiktok.test_event_code');
    }

    public function enabled(): bool
    {
        return !empty($this->pixelId) && !empty($this->accessToken);
    }

    /**
     * Track an event via TikTok Events API.
     *
     * @param  string       $event
     * @param  array        $properties
     * @param  Request|null $request
     * @param  array        $userData
     * @param  array        $context
     * @param  string|null  $eventId
     */
    public function track(
        string $event,
        array $properties = [],
        ?Request $request = null,
        array $userData = [],
        array $context = [],
        ?string $eventId = null
    ): void {
        if (!$this->enabled()) {
            return;
        }

        $payload = $this->buildPayload(
            event: $event,
            properties: $properties,
            request: $request,
            userData: $userData,
            context: $context,
            eventId: $eventId
        );

        try {
            $response = Http::retry(2, 200)
                ->withHeaders([
                    'Access-Token' => $this->accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint, $payload);

            if ($response->failed()) {
                Log::warning('TikTok Events API request failed', [
                    'event' => $event,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('TikTok Events API request exception', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function buildPayload(
        string $event,
        array $properties,
        ?Request $request,
        array $userData,
        array $context,
        ?string $eventId
    ): array {
        $timestamp = now()->timestamp;
        $eventId = $eventId ?? Str::uuid()->toString();

        $user = $this->formatUserData($userData, $request);
        $context = $this->formatContext($context, $request);
        $properties = $this->formatProperties($properties);

        $payload = array_filter([
            'pixel_code' => $this->pixelId,
            'event' => $event,
            'event_id' => $eventId,
            'timestamp' => $timestamp,
            'properties' => $properties,
            'user' => $user,
            'context' => $context,
        ], function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }

            return $value !== null;
        });

        if ($this->testEventCode) {
            $payload['test_event_code'] = $this->testEventCode;
        }

        return $payload;
    }

    private function formatProperties(array $properties): array
    {
        $defaults = [
            'currency' => 'USD',
        ];

        $properties = array_merge($defaults, $properties);

        // Ensure contents items are formatted correctly if provided.
        if (isset($properties['contents']) && is_array($properties['contents'])) {
            $properties['contents'] = array_map(function ($item) {
                return array_filter([
                    'content_id' => Arr::get($item, 'content_id'),
                    'content_type' => Arr::get($item, 'content_type'),
                    'content_name' => Arr::get($item, 'content_name'),
                    'quantity' => Arr::get($item, 'quantity'),
                    'price' => Arr::get($item, 'price'),
                ], function ($value) {
                    return $value !== null && $value !== '';
                });
            }, $properties['contents']);
        }

        return array_filter($properties, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }

            return $value !== null;
        });
    }

    private function formatUserData(array $userData, ?Request $request): array
    {
        $user = [];

        if (!empty($userData['email'])) {
            $user['email'] = [$this->hashValue($userData['email'])];
        }

        if (!empty($userData['phone'])) {
            $normalizedPhone = preg_replace('/\D+/', '', $userData['phone']);
            if (!empty($normalizedPhone)) {
                $user['phone_number'] = [$this->hashValue($normalizedPhone)];
            }
        }

        if (!empty($userData['external_id'])) {
            $user['external_id'] = [$this->hashValue($userData['external_id'])];
        }

        if ($request) {
            $ip = $request->ip();
            if ($ip) {
                $user['ip'] = $ip;
            }

            $userAgent = $request->header('User-Agent');
            if ($userAgent) {
                $user['user_agent'] = $userAgent;
            }

            $ttclid = $request->cookie('ttclid');
            if ($ttclid) {
                $user['ttclid'] = $ttclid;
            }

            $ttp = $request->cookie('ttp');
            if ($ttp) {
                $user['ttp'] = $ttp;
            }
        }

        return $user;
    }

    private function formatContext(array $context, ?Request $request): array
    {
        $formatted = $context;

        if ($request) {
            $pageContext = $formatted['page'] ?? [];
            $pageContext['url'] = $pageContext['url'] ?? $request->fullUrl();
            $referer = $request->headers->get('referer');
            if ($referer) {
                $pageContext['referrer'] = $pageContext['referrer'] ?? $referer;
            }

            $formatted['page'] = $pageContext;
        }

        return array_filter($formatted, function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }

            return $value !== null;
        });
    }

    private function hashValue(string $value): string
    {
        $normalized = strtolower(trim($value));

        if (preg_match('/^[a-f0-9]{64}$/', $normalized)) {
            return $normalized;
        }

        return hash('sha256', $normalized);
    }
}

