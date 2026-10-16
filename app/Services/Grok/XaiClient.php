<?php

namespace App\Services\Grok;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class XaiClient
{
    public function complete(string $system, string $user): GrokReply
    {
        $key = (string) config('services.xai.api_key');
        if ($key === '') {
            throw new RuntimeException('Chưa cấu hình XAI_API_KEY trên server.');
        }

        $model = (string) config('services.xai.model', 'grok-4.7');
        if ($model === '') {
            $model = 'grok-4.7';
        }

        $response = $this->send($key, $model, $system, $user);
        if ($this->modelRejected($response)) {
            $next = $this->currentTextModel($key);
            if ($next === $model) {
                throw new RuntimeException($this->publicError($response));
            }
            $model = $next;
            $response = $this->send($key, $model, $system, $user);
        }

        if (! $response->successful()) {
            throw new RuntimeException($this->publicError($response));
        }

        $content = $response->json('choices.0.message.content');
        $content = $this->textContent($content);
        if ($content === '') {
            throw new RuntimeException('Grok không trả nội dung.');
        }

        $usedModel = $response->json('model');
        if (! is_string($usedModel) || $usedModel === '') {
            $usedModel = $model;
        }

        return new GrokReply($content, $usedModel);
    }

    private function send(string $key, string $model, string $system, string $user): Response
    {
        $base = rtrim((string) config('services.xai.base_url', 'https://api.x.ai/v1'), '/');

        return Http::withToken($key)
            ->acceptJson()
            ->timeout(90)
            ->post($base.'/chat/completions', [
                'model' => $model,
                'reasoning_effort' => 'low',
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);
    }

    private function modelRejected(Response $response): bool
    {
        if (! in_array($response->status(), [400, 404, 422], true)) {
            return false;
        }

        return str_contains(strtolower($response->body()), 'model');
    }

    private function currentTextModel(string $key): string
    {
        $base = rtrim((string) config('services.xai.base_url', 'https://api.x.ai/v1'), '/');
        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(30)
            ->get($base.'/models');

        if (! $response->successful()) {
            throw new RuntimeException($this->publicError($response));
        }

        $models = $response->json('data');
        if (! is_array($models)) {
            throw new RuntimeException('API xAI không trả danh sách model.');
        }

        $bestId = null;
        $bestCreated = -1;
        foreach ($models as $model) {
            if (! is_array($model)) {
                continue;
            }
            $id = $model['id'] ?? '';
            if (! is_string($id) || ! str_starts_with($id, 'grok-')) {
                continue;
            }
            if (preg_match('/imagine|voice|video|image|tts|asr|embed/i', $id) === 1) {
                continue;
            }
            $created = (int) ($model['created'] ?? 0);
            if ($bestId === null || $created >= $bestCreated) {
                $bestId = $id;
                $bestCreated = $created;
            }
        }

        if ($bestId === null) {
            throw new RuntimeException('API xAI không có model Grok văn bản đang dùng.');
        }

        return $bestId;
    }

    private function textContent(mixed $content): string
    {
        if (is_string($content)) {
            return trim($content);
        }

        if (! is_array($content)) {
            return '';
        }

        $parts = [];
        foreach ($content as $part) {
            if (is_string($part)) {
                $parts[] = $part;
            } elseif (is_array($part)) {
                $parts[] = (string) ($part['text'] ?? '');
            }
        }

        return trim(implode('', $parts));
    }

    private function publicError(Response $response): string
    {
        $error = $response->json('error');
        if (is_array($error)) {
            $error = $error['message'] ?? $error['code'] ?? '';
        }
        if (! is_string($error) || $error === '') {
            $code = $response->json('code');
            $error = is_string($code) ? $code : '';
        }

        $error = preg_replace('/xai-[A-Za-z0-9_\-]+/', '[ẩn]', $error) ?? '';
        $error = preg_replace('/Bearer\s+\S+/i', '[ẩn]', $error) ?? '';
        $error = preg_replace('#https?://\S+#', '', $error) ?? '';
        $error = trim(preg_replace('/\s+/', ' ', $error) ?? '');
        $error = trim(mb_substr($error, 0, 300));

        return 'Grok HTTP '.$response->status().($error !== '' ? ': '.$error : '.');
    }
}
