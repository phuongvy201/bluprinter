<?php

namespace App\Services;

use App\Support\S3Media;
use App\Support\StudioAiSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StudioAiService
{
    public function isConfigured(): bool
    {
        return filled(config('studio.ai.api_key')) || filled(config('studio.ai.completion_api_key'));
    }

    public function isEnabled(): bool
    {
        return $this->isConfigured() && (bool) StudioAiSettings::resolved()['enabled'];
    }

    public function improvePrompt(string $prompt, array $referenceUrls = []): string
    {
        $this->assertAvailable();

        $idea = trim($prompt);
        $referenceUrls = $this->normalizeUrls($referenceUrls);
        if ($idea === '' && $referenceUrls === []) {
            throw new RuntimeException('Describe what you want, or add a reference photo.');
        }
        if ($idea === '') {
            $idea = 'Create a t-shirt chest graphic based on these reference photos.';
        }

        $text = $this->writePromptViaChat($idea, $referenceUrls);

        return $text !== '' ? $text : $idea;
    }

    /**
     * @param  array<int, string>  $referenceUrls
     * @return array<int, array{url: string, prompt: string}>
     */
    public function generateDesigns(string $prompt, array $referenceUrls = [], int $count = 2): array
    {
        $this->assertAvailable();

        $finalPrompt = trim($prompt);
        $referenceUrls = $this->normalizeUrls($referenceUrls);
        if ($finalPrompt === '' && $referenceUrls === []) {
            throw new RuntimeException('Please describe the design you want, or add a reference photo.');
        }
        if ($finalPrompt === '') {
            $finalPrompt = 'Create a t-shirt chest graphic that closely matches the reference photos.';
        }

        $count = max(1, min((int) StudioAiSettings::resolved()['image_count'], $count));

        if ($referenceUrls !== []) {
            $described = $this->describeReferences($referenceUrls);
            if ($described !== '') {
                $finalPrompt .= '. Match these reference photos closely: '.$described.'. Keep the same subject, colors, composition, and art style.';
            } else {
                $finalPrompt .= '. Follow the attached reference photos closely: same subject, colors, composition, and art style.';
            }
        }

        $suffix = trim((string) (StudioAiSettings::resolved()['design_suffix'] ?? ''));
        if ($suffix !== '') {
            $finalPrompt .= '. '.$suffix;
        }

        if ($referenceUrls !== [] && $this->supportsImageEdits()) {
            $payload = $this->imagePayload($finalPrompt, $count);
            $response = $this->requestImageEdits($payload, $referenceUrls);
            if ($response->successful()) {
                $items = $this->storeGeneratedImages($response->json(), $finalPrompt);
                if ($items !== []) {
                    return $items;
                }
            } else {
                Log::warning('Studio AI image edits failed; generating from described references', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }

        if (!$this->supportsNativeCount()) {
            $results = [];
            for ($i = 0; $i < $count; $i++) {
                $results[] = $this->generateOne($finalPrompt);
            }

            return $results;
        }

        $payload = $this->imagePayload($finalPrompt, $count);
        $response = $this->http()->post($this->endpoint('images/generations'), $payload);

        if (!$response->successful()) {
            Log::warning('Studio AI generate failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException($this->friendlyImageError($this->extractErrorMessage($response)));
        }

        $items = $this->storeGeneratedImages($response->json(), $finalPrompt);
        if ($items === []) {
            throw new RuntimeException('AI did not return an image.');
        }

        return $items;
    }

    /**
     * Photorealistic virtual try-on from a customer photo and a product image.
     *
     * @return array{url: string, prompt: string}
     */
    public function generateTryOn(
        string $personUrl,
        string $garmentUrl,
        string $productName = '',
        ?string $backUrl = null,
        string $view = 'front',
        string $productType = '',
    ): array {
        $this->assertAvailable();

        $personUrl = $this->normalizeUrls([$personUrl])[0] ?? '';
        $garmentUrl = $this->normalizeUrls([$garmentUrl])[0] ?? '';
        $backUrl = $this->normalizeUrls([(string) $backUrl])[0] ?? '';
        if ($personUrl === '' || $garmentUrl === '') {
            throw new RuntimeException('Upload a photo and choose a product first.');
        }

        $view = $view === 'back' ? 'back' : 'front';
        $label = trim($productName);
        $kind = $this->inferTryOnKind($label, $productType);
        $typeLabel = $productType !== '' ? $productType : str_replace('_', ' ', $kind);
        $template = (string) (StudioAiSettings::resolved()['try_on_prompt'] ?? '');
        $productBit = $label !== '' ? ' named "'.$label.'"' : '';
        $prompt = trim(str_replace(
            ['{product_name}', '{product_type}'],
            [$productBit, $typeLabel],
            $template
        ));
        if ($prompt === '') {
            $prompt = StudioAiSettings::defaults()['try_on_prompt'];
            $prompt = trim(str_replace(
                ['{product_name}', '{product_type}'],
                [$productBit, $typeLabel],
                $prompt
            ));
        }

        $prompt .= ' '.$this->tryOnViewInstruction($kind, $view);

        $references = [$personUrl, $garmentUrl];
        if ($backUrl !== '' && $backUrl !== $garmentUrl) {
            $references[] = $backUrl;
            $prompt .= $view === 'back'
                ? ' The last product image is the BACK of the product — use that artwork only on the back/rear of the item.'
                : ' The last product image is the BACK of the product for reference only — do not place that artwork on the front of the item or on the customer\'s clothing.';
        }

        if ($this->supportsImageEdits()) {
            $response = $this->requestImageEdits($this->imagePayload($prompt, 1), $references);
            if ($response->successful()) {
                $items = $this->storeGeneratedImages($response->json(), $prompt);
                if ($items !== []) {
                    return $items[0];
                }
            }

            Log::warning('Studio AI try-on edits failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException($this->friendlyImageError($this->extractErrorMessage($response)) ?: 'Could not create the try-on image. Please try another photo.');
        }

        throw new RuntimeException('Virtual try-on needs an image-edit model. Check Studio AI settings.');
    }

    protected function inferTryOnKind(string $name, string $type = ''): string
    {
        $hay = strtolower(trim($name.' '.$type));
        if (preg_match('/phone\s*case|phonecase|iphone case|samsung case/', $hay)) {
            return 'phone_case';
        }
        if (preg_match('/\bhat\b|\bcap\b|beanie|snapback|trucker/', $hay)) {
            return 'hat';
        }
        if (preg_match('/mug|tumbler|cup|bottle/', $hay)) {
            return 'mug';
        }
        if (preg_match('/tote|bag|backpack|pouch/', $hay)) {
            return 'bag';
        }
        if (preg_match('/hoodie|sweatshirt|t-?shirt|\btee\b|tank|sweater|pullover|shirt/', $hay)) {
            return 'apparel';
        }

        return 'product';
    }

    protected function tryOnViewInstruction(string $kind, string $view): string
    {
        if ($kind === 'phone_case') {
            return $view === 'back'
                ? 'IMPORTANT: Show the customer holding a phone so the BACK of the phone case (the printed design) faces the camera. Artwork stays on the case only.'
                : 'IMPORTANT: Place a realistic phone with this case in the customer\'s hand. Front view of the person; the case design belongs on the phone case, never printed onto a shirt or chest.';
        }
        if ($kind === 'hat') {
            return $view === 'back'
                ? 'IMPORTANT: Place the hat/cap on the customer\'s head and show a BACK/3-quarter view so the rear of the hat is visible. Do not put hat graphics on clothing.'
                : 'IMPORTANT: Place the hat/cap on the customer\'s head. Show the FRONT panel/logo of the hat. Correct scale for a human head. Do not put hat graphics on the chest.';
        }
        if ($kind === 'mug') {
            return 'IMPORTANT: The customer is holding this mug/tumbler. Keep the print on the drinkware, with natural perspective and hand grip.';
        }
        if ($kind === 'bag') {
            return $view === 'back'
                ? 'IMPORTANT: The customer is carrying this bag; show the back of the bag if useful. Print stays on the bag, not on clothing.'
                : 'IMPORTANT: The customer is carrying this tote/bag on the shoulder or in hand. Print stays on the bag.';
        }
        if ($kind === 'apparel') {
            return $view === 'back'
                ? 'IMPORTANT: Generate a BACK VIEW of the same person wearing this garment. Large back print only on the back. Do not put the back graphic on the chest.'
                : 'IMPORTANT: FRONT VIEW of the person wearing the garment. If the mockup is 2-panel, LEFT is FRONT and RIGHT is BACK. Use only the FRONT print on the chest.';
        }

        return $view === 'back'
            ? 'IMPORTANT: Show the back/rear of this product in use with the same customer. Do not flatten the design onto unrelated clothing.'
            : 'IMPORTANT: Show this product in a natural front-facing use with the customer. Match the product category from the name. Do not print accessory artwork onto a t-shirt.';
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function storeReferenceImages(array $files): array
    {
        $urls = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $stored = S3Media::store($file, 'studio/references');
            if ($stored) {
                $urls[] = $stored;
            }
        }

        return $urls;
    }

    /**
     * @return array{url: string, prompt: string}
     */
    protected function generateOne(string $prompt): array
    {
        $response = $this->http()->post($this->endpoint('images/generations'), $this->imagePayload($prompt, 1));

        if (!$response->successful()) {
            Log::warning('Studio AI generate failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException($this->friendlyImageError($this->extractErrorMessage($response)));
        }

        $items = $this->storeGeneratedImages($response->json(), $prompt);
        if ($items === []) {
            throw new RuntimeException('AI did not return an image.');
        }

        return $items[0];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $referenceUrls
     */
    protected function requestImageEdits(array $payload, array $referenceUrls): Response
    {
        $pending = $this->http();
        $attached = 0;
        foreach (array_slice($referenceUrls, 0, 4) as $url) {
            $fetched = $this->fetchReference($url);
            if (!$fetched) {
                continue;
            }
            $field = $attached === 0 ? 'image' : 'image[]';
            $pending = $pending->attach($field, $fetched['binary'], $fetched['filename']);
            $attached++;
        }

        if ($attached === 0) {
            $payload['images'] = array_map(
                fn (string $url) => ['image_url' => $url],
                array_slice($referenceUrls, 0, 4),
            );

            return $this->http()->post($this->endpoint('images/edits'), $payload);
        }

        $form = [
            'model' => $payload['model'],
            'prompt' => $payload['prompt'],
            'n' => (string) ($payload['n'] ?? 1),
            'response_format' => $payload['response_format'] ?? 'b64_json',
        ];
        if (!empty($payload['size'])) {
            $form['size'] = $payload['size'];
        }

        return $pending->post($this->endpoint('images/edits'), $form);
    }

    /**
     * @return array<string, mixed>
     */
    protected function imagePayload(string $prompt, int $count): array
    {
        $payload = [
            'model' => $this->imageModel(),
            'prompt' => $prompt,
            'n' => $count,
            'response_format' => 'b64_json',
        ];

        if ($this->sendsImageSize()) {
            $payload['size'] = $this->imageSize();
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @return array<int, array{url: string, prompt: string}>
     */
    protected function storeGeneratedImages(?array $json, string $prompt): array
    {
        $rows = data_get($json, 'data');
        if (!is_array($rows) || $rows === []) {
            $rows = data_get($json, 'images');
        }
        if (!is_array($rows)) {
            return [];
        }

        $results = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $stored = $this->storeGeneratedImage($row, $prompt);
            if ($stored) {
                $results[] = $stored;
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{url: string, prompt: string}|null
     */
    protected function storeGeneratedImage(array $row, string $prompt): ?array
    {
        $b64 = (string) ($row['b64_json'] ?? $row['b64'] ?? '');
        $remoteUrl = (string) ($row['url'] ?? $row['image_url'] ?? '');

        if ($b64 !== '') {
            if (str_contains($b64, ',')) {
                $b64 = substr($b64, strpos($b64, ',') + 1);
            }
            $binary = base64_decode($b64, true);
            if ($binary === false || $binary === '') {
                return null;
            }
            $stored = S3Media::storeContents($binary, 'studio/ai', $this->guessExtension($binary));
            if (!$stored) {
                throw new RuntimeException('Could not save the generated design.');
            }

            return ['url' => $stored, 'prompt' => $prompt];
        }

        if ($remoteUrl === '') {
            return null;
        }

        $downloaded = Http::timeout(60)->get($remoteUrl);
        if ($downloaded->successful()) {
            $body = $downloaded->body();
            $stored = S3Media::storeContents($body, 'studio/ai', $this->guessExtension($body));
            if ($stored) {
                return ['url' => $stored, 'prompt' => $prompt];
            }
        }

        return ['url' => $remoteUrl, 'prompt' => $prompt];
    }

    /**
     * @param  array<int, string>  $urls
     */
    protected function describeReferences(array $urls): string
    {
        $urls = $this->normalizeUrls($urls);
        if ($urls === []) {
            return '';
        }

        $content = $this->visionUserContent(
            (string) (StudioAiSettings::resolved()['describe_references_prompt'] ?? 'Look at these reference photos carefully. Write a detailed visual brief.'),
            $urls
        );

        try {
            $response = $this->http(60)->post($this->endpoint('chat/completions'), [
                'model' => $this->chatModel(),
                'max_tokens' => 220,
                'messages' => [
                    ['role' => 'user', 'content' => $content],
                ],
            ]);

            if ($response->successful()) {
                return $this->extractCompletionText($response->json());
            }

            Log::info('Studio AI reference describe failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::info('Studio AI reference describe skipped', ['error' => $e->getMessage()]);
        }

        return '';
    }

    protected function promptWriterInstruction(bool $hasReferences): string
    {
        $settings = StudioAiSettings::resolved();
        $text = trim((string) ($settings['improve_system_prompt'] ?? ''));
        if ($text === '') {
            $text = StudioAiSettings::defaults()['improve_system_prompt'];
        }
        if ($hasReferences) {
            $note = trim((string) ($settings['improve_references_note'] ?? ''));
            if ($note !== '') {
                $text .= "\n".$note;
            }
        }

        return $text;
    }

    /**
     * @param  array<int, string>  $referenceUrls
     */
    protected function writePromptViaChat(string $idea, array $referenceUrls = []): string
    {
        $url = $this->endpoint('chat/completions');
        $userText = $idea;
        if ($referenceUrls !== []) {
            $userText .= "\n\nUse the attached reference photos as the main subject and style. Name specific objects, colors, and composition you see.";
        }

        $response = $this->http(90)->post($url, [
            'model' => $this->chatModel(),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->promptWriterInstruction($referenceUrls !== []),
                ],
                [
                    'role' => 'user',
                    'content' => $this->visionUserContent($userText, $referenceUrls),
                ],
            ],
            'max_tokens' => 280,
            'temperature' => 0.4,
            'n' => 1,
            'stream' => false,
        ]);

        if (!$response->successful()) {
            Log::warning('Studio AI chat/completions failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException($this->friendlyImageError($this->extractErrorMessage($response)) ?: 'Could not write a design prompt. Please try again.');
        }

        $text = $this->extractCompletionText($response->json());
        if ($text === '') {
            throw new RuntimeException('Chat Completions API did not return text. Use a text model (not gpt-image-2) for STUDIO_AI_CHAT_MODEL.');
        }

        return $text;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function extractCompletionText(?array $json): string
    {
        $content = data_get($json, 'choices.0.message.content');
        if (is_array($content)) {
            $parts = [];
            foreach ($content as $part) {
                if (is_string($part)) {
                    $parts[] = $part;
                } elseif (is_array($part) && isset($part['text'])) {
                    $parts[] = (string) $part['text'];
                }
            }
            $text = trim(implode("\n", $parts));
        } else {
            $text = trim((string) ($content ?: data_get($json, 'choices.0.text') ?: ''));
        }

        $text = trim($text, " \t\n\r\0\x0B\"'`");
        if (str_starts_with($text, '```')) {
            $text = trim(preg_replace('/^```[a-zA-Z]*\n?|\n?```$/', '', $text) ?? $text);
        }

        return $text;
    }

    protected function completionHttp(?int $timeout = null)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->completionApiKey(),
            'Content-Type' => 'application/json',
        ])
            ->acceptJson()
            ->timeout($timeout ?? $this->requestTimeout())
            ->connectTimeout(20);
    }

    protected function completionBaseUrl(): string
    {
        $url = trim((string) config('studio.ai.completion_base_url'));
        if ($url === '') {
            $url = $this->baseUrl();
        }

        return rtrim($url, '/');
    }

    protected function completionApiKey(): string
    {
        $key = trim((string) config('studio.ai.completion_api_key'));
        if ($key !== '') {
            return $key;
        }

        return trim((string) config('studio.ai.api_key'));
    }

    protected function http(?int $timeout = null)
    {
        return Http::withToken((string) config('studio.ai.api_key'))
            ->acceptJson()
            ->timeout($timeout ?? $this->requestTimeout())
            ->connectTimeout(20);
    }

    protected function endpoint(string $path): string
    {
        return rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');
    }

    protected function baseUrl(): string
    {
        $url = trim((string) config('studio.ai.base_url', 'https://api.openai.com/v1'));

        return $url !== '' ? rtrim($url, '/') : 'https://api.openai.com/v1';
    }

    protected function isOfficialOpenAi(): bool
    {
        $host = strtolower((string) parse_url($this->baseUrl(), PHP_URL_HOST));

        return $host === 'api.openai.com';
    }

    protected function imageModel(): string
    {
        $fromSettings = trim((string) (StudioAiSettings::resolved()['image_model'] ?? ''));
        if ($fromSettings !== '') {
            return $fromSettings;
        }

        $configured = trim((string) config('studio.ai.image_model'));
        if ($configured !== '') {
            return $configured;
        }

        return $this->isOfficialOpenAi() ? 'dall-e-3' : 'gpt-image-2';
    }

    protected function imageSize(): string
    {
        $fromSettings = trim((string) (StudioAiSettings::resolved()['image_size'] ?? ''));
        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return (string) config('studio.ai.image_size', '1024x1024');
    }

    protected function requestTimeout(): int
    {
        return (int) (StudioAiSettings::resolved()['timeout'] ?? config('studio.ai.timeout', 180));
    }

    protected function completionModel(): string
    {
        $configured = trim((string) config('studio.ai.completion_model'));
        if ($configured !== '') {
            return $configured;
        }

        return $this->chatModel();
    }

    protected function chatModel(): string
    {
        $fromSettings = trim((string) (StudioAiSettings::resolved()['chat_model'] ?? ''));
        if ($fromSettings !== '') {
            return $fromSettings;
        }

        $configured = trim((string) config('studio.ai.chat_model'));

        return $configured !== '' ? $configured : 'gpt-4o-mini';
    }

    protected function supportsNativeCount(): bool
    {
        $model = strtolower($this->imageModel());

        return !str_starts_with($model, 'dall-e-3');
    }

    protected function supportsImageEdits(): bool
    {
        return !$this->isOfficialOpenAi() || str_starts_with(strtolower($this->imageModel()), 'gpt-image');
    }

    protected function sendsImageSize(): bool
    {
        $model = strtolower($this->imageModel());

        return str_starts_with($model, 'dall-e');
    }

    protected function guessExtension(string $binary): string
    {
        $header = substr($binary, 0, 12);
        if (str_starts_with($header, "\x89PNG")) {
            return 'png';
        }
        if (str_starts_with($header, "\xFF\xD8\xFF")) {
            return 'jpg';
        }
        if (str_starts_with($header, 'RIFF') && str_contains(substr($header, 0, 12), 'WEBP')) {
            return 'webp';
        }

        return 'png';
    }

    /**
     * @param  array<int, mixed>  $urls
     * @return array<int, string>
     */
    protected function normalizeUrls(array $urls): array
    {
        return array_values(array_filter($urls, fn ($url) => is_string($url) && $url !== '' && strlen($url) < 2000));
    }

    /**
     * @param  array<int, string>  $urls
     * @return array<int, array<string, mixed>>|string
     */
    protected function visionUserContent(string $text, array $urls): array|string
    {
        $parts = [
            ['type' => 'text', 'text' => $text],
        ];
        foreach (array_slice($urls, 0, 4) as $url) {
            $fetched = $this->fetchReference($url);
            $imageUrl = $fetched['data_url'] ?? null;
            if ($imageUrl === null || $imageUrl === '') {
                continue;
            }
            $parts[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $imageUrl],
            ];
        }

        return count($parts) === 1 ? $text : $parts;
    }

    /**
     * @return array{binary: string, mime: string, filename: string, data_url: ?string}|null
     */
    protected function fetchReference(string $url): ?array
    {
        $binary = $this->readReferenceBytes($url);
        if ($binary === null || $binary === '' || strlen($binary) < 32) {
            return null;
        }

        $ext = $this->guessExtension($binary);
        $mime = $this->guessMime($binary);
        $dataUrl = null;
        if (strlen($binary) <= 3500000) {
            $dataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);
        }

        return [
            'binary' => $binary,
            'mime' => $mime,
            'filename' => 'reference.'.$ext,
            'data_url' => $dataUrl,
        ];
    }

    protected function readReferenceBytes(string $url): ?string
    {
        if (S3Media::isPublicUrl($url)) {
            $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
            $key = ltrim((string) preg_replace('#^/image\.bluprinter/#', '', $path), '/');
            if ($key !== '') {
                try {
                    $contents = Storage::disk('s3')->get($key);
                    if (is_string($contents) && $contents !== '') {
                        return $contents;
                    }
                } catch (\Throwable $e) {
                    Log::info('Studio AI S3 reference read failed', ['error' => $e->getMessage()]);
                }
            }
        }

        $storagePrefixes = [
            rtrim((string) asset('storage'), '/').'/',
            rtrim((string) config('app.url'), '/').'/storage/',
        ];
        foreach ($storagePrefixes as $prefix) {
            if ($prefix === '/' || !str_starts_with($url, $prefix)) {
                continue;
            }
            $relative = ltrim(substr($url, strlen($prefix)), '/');
            if ($relative === '' || str_contains($relative, '..')) {
                continue;
            }
            try {
                $contents = Storage::disk('public')->get($relative);
                if (is_string($contents) && $contents !== '') {
                    return $contents;
                }
            } catch (\Throwable) {
                // Try HTTP next.
            }
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'image/*,*/*'])
                ->get($url);
            if (!$response->successful()) {
                Log::info('Studio AI could not download reference', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::info('Studio AI reference download skipped', ['error' => $e->getMessage()]);

            return null;
        }
    }

    protected function guessMime(string $binary): string
    {
        $ext = $this->guessExtension($binary);

        return match ($ext) {
            'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }

    protected function extractErrorMessage(Response $response): ?string
    {
        $json = $response->json();
        $message = data_get($json, 'error.message')
            ?? data_get($json, 'message')
            ?? data_get($json, 'detail')
            ?? data_get($json, 'error');

        return is_string($message) && $message !== '' ? $message : null;
    }

    protected function assertAvailable(): void
    {
        if (!$this->isEnabled()) {
            throw new RuntimeException('AI design generation is turned off. You can still upload a design or pick one from the library.');
        }
    }

    protected function friendlyImageError(?string $message): string
    {
        $message = trim((string) $message);
        if ($message === '') {
            return 'Could not generate a design. Please try a different prompt.';
        }

        $lower = strtolower($message);
        if (
            str_contains($lower, 'unauthorized')
            || str_contains($lower, 'auth-key')
            || str_contains($lower, 'invalid api key')
            || str_contains($lower, 'authentication_error')
            || str_contains($message, '密钥')
        ) {
            return 'AI gateway rejected the API key. Use the key issued by that host in STUDIO_AI_API_KEY.';
        }
        if (str_contains($lower, 'model_not_found') || str_contains($lower, 'no available channel for model')) {
            return 'This New API host has no channel for the current chat model. Set STUDIO_AI_CHAT_MODEL (and STUDIO_AI_COMPLETION_MODEL) to a model enabled in that dashboard.';
        }
        if (str_contains($lower, 'no available') || str_contains($lower, 'no account') || str_contains($lower, 'token')) {
            return 'The AI gateway has no available account right now. Try again later.';
        }

        return $message;
    }
}
