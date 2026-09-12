<?php

namespace App\Support;

class StudioAiSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function resolved(): array
    {
        $defaults = self::defaults();
        $stored = Settings::get('studio.ai.config');
        if (!$stored) {
            return $defaults;
        }

        $decoded = is_array($stored) ? $stored : json_decode((string) $stored, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $merged = array_replace($defaults, $decoded);
        $merged['enabled'] = (bool) ($merged['enabled'] ?? true);
        $merged['try_on_enabled'] = (bool) ($merged['try_on_enabled'] ?? true);
        $merged['image_count'] = max(1, min(4, (int) ($merged['image_count'] ?? 2)));
        $merged['timeout'] = max(30, min(300, (int) ($merged['timeout'] ?? 180)));
        $merged['prompt_max'] = max(200, min(2000, (int) ($merged['prompt_max'] ?? 1000)));
        $merged['max_references'] = max(1, min(8, (int) ($merged['max_references'] ?? 4)));
        $merged['generate_per_minute'] = max(1, min(60, (int) ($merged['generate_per_minute'] ?? 6)));
        $merged['improve_per_minute'] = max(1, min(60, (int) ($merged['improve_per_minute'] ?? 12)));
        $merged['try_on_per_minute'] = max(1, min(60, (int) ($merged['try_on_per_minute'] ?? 6)));
        $merged['inspiration_prompts'] = self::normalizePrompts($merged['inspiration_prompts'] ?? []);
        $merged['try_on_models'] = self::normalizeTryOnModels($merged['try_on_models'] ?? []);
        foreach (['improve_system_prompt', 'improve_references_note', 'design_suffix', 'try_on_prompt', 'describe_references_prompt'] as $key) {
            $merged[$key] = trim((string) ($merged[$key] ?? $defaults[$key]));
            if ($merged[$key] === '' || ($key === 'try_on_prompt' && self::isLegacyTryOnPrompt($merged[$key]))) {
                $merged[$key] = $defaults[$key];
            }
        }

        return $merged;
    }

    public static function save(array $data): void
    {
        Settings::set('studio.ai.config', json_encode($data));
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        $prompts = config('studio.inspiration_prompts', []);

        return [
            'enabled' => true,
            'try_on_enabled' => true,
            'image_model' => (string) config('studio.ai.image_model', ''),
            'chat_model' => (string) config('studio.ai.chat_model', ''),
            'image_size' => (string) config('studio.ai.image_size', '1024x1024'),
            'image_count' => (int) config('studio.ai.image_count', 2),
            'timeout' => (int) config('studio.ai.timeout', 180),
            'prompt_max' => 1000,
            'max_references' => 4,
            'generate_per_minute' => 6,
            'improve_per_minute' => 12,
            'try_on_per_minute' => 6,
            'try_on_models' => self::defaultTryOnModels(),
            'inspiration_prompts' => is_array($prompts) ? array_values($prompts) : [],
            'improve_system_prompt' => "Write ONE English image-generation prompt for a t-shirt chest graphic.\nUnder 500 characters. Isolated graphic on a transparent or plain background.\nNo photorealistic garment, no mockup, no watermark, no extra commentary.\nReturn only the prompt.",
            'improve_references_note' => 'The user attached reference photos. You MUST describe the actual subject, colors, composition, and style visible in those photos. Do not invent an unrelated scene.',
            'design_suffix' => 'Isolated graphic on a transparent or plain background, suitable as a t-shirt chest print, no garment mockup, no watermark.',
            'try_on_prompt' => 'Photorealistic virtual try-on / product visualization. Image 1 is the customer photo — keep their face, identity, body, pose, lighting, and background. Later images are the product{product_name} ({product_type}). Place that exact product on or with the customer in a natural real-life way for its category: apparel is worn on the body; a hat/cap sits on the head; a phone case is on a phone the customer is holding (artwork stays on the case, never on clothing); a mug/tumbler is held in the hand; a tote/bag is carried. If a product mockup shows two views in one frame, LEFT is usually the FRONT and RIGHT is the BACK — use only the side that matches this shot. Do not collage or paste a flat cutout. Do not put the print on the wrong surface. No extra people, no extra text, no watermark.',
            'describe_references_prompt' => 'Look at these reference photos carefully. Write a detailed visual brief (max 90 words) covering subject, pose, colors, art style, and important details. No preamble. This brief will generate a matching t-shirt graphic.',
        ];
    }

    /**
     * @param  mixed  $prompts
     * @return array<int, string>
     */
    public static function normalizePrompts(mixed $prompts): array
    {
        if (is_string($prompts)) {
            $prompts = preg_split('/\r\n|\r|\n/', $prompts) ?: [];
        }
        if (!is_array($prompts)) {
            return [];
        }

        return collect($prompts)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, label: string, src: string}>
     */
    public static function defaultTryOnModels(): array
    {
        return [
            ['id' => 'man', 'label' => 'Man', 'src' => '/images/studio/models/man.jpg'],
            ['id' => 'woman', 'label' => 'Woman', 'src' => '/images/studio/models/woman.jpg'],
            ['id' => 'child', 'label' => 'Child', 'src' => '/images/studio/models/child.jpg'],
        ];
    }

    /**
     * @return array<int, array{id: string, label: string, src: string}>
     */
    public static function tryOnModels(): array
    {
        return self::resolved()['try_on_models'];
    }

    /**
     * @param  mixed  $models
     * @return array<int, array{id: string, label: string, src: string}>
     */
    public static function normalizeTryOnModels(mixed $models): array
    {
        if (! is_array($models) || $models === []) {
            $models = self::defaultTryOnModels();
        }

        $out = [];
        foreach (array_values($models) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $src = trim((string) ($row['src'] ?? ''));
            if ($src === '') {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $id = trim((string) ($row['id'] ?? ''));
            $out[] = [
                'id' => $id !== '' ? $id : 'model-'.($index + 1),
                'label' => $label !== '' ? $label : 'Model',
                'src' => self::absoluteTryOnModelUrl($src),
                'path' => $src,
            ];
        }

        if ($out === []) {
            foreach (self::defaultTryOnModels() as $index => $row) {
                $out[] = [
                    'id' => $row['id'],
                    'label' => $row['label'],
                    'src' => self::absoluteTryOnModelUrl($row['src']),
                    'path' => $row['src'],
                ];
            }
        }

        return $out;
    }

    public static function absoluteTryOnModelUrl(string $src): string
    {
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, '//')) {
            return $src;
        }

        return asset(ltrim($src, '/'));
    }

    protected static function isLegacyTryOnPrompt(string $prompt): bool
    {
        $mentionsPhoneOrHat = str_contains($prompt, 'phone case') || str_contains($prompt, '{product_type}');
        if ($mentionsPhoneOrHat) {
            return false;
        }

        return str_contains($prompt, 'The second image is the product')
            || str_contains($prompt, 'onto the visible chest')
            || str_contains($prompt, 'LEFT = FRONT of the garment');
    }
}
