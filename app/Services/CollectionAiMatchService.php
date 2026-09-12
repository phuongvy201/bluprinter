<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use App\Support\StudioAiSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CollectionAiMatchService
{
    /**
     * @var array<int, array<int, int>>
     */
    protected array $matchedProductIds = [];

    public function __construct(
        protected CollectionKeywordSyncService $keywordSync
    ) {}

    public function enabled(): bool
    {
        if (! config('catalog.collection_ai.enabled', true)) {
            return false;
        }

        $key = trim((string) config('studio.ai.completion_api_key') ?: (string) config('studio.ai.api_key'));

        return $key !== '' && $this->chatModel() !== '';
    }

    /**
     * @return array<int, int>
     */
    public function matchProduct(Product $product): array
    {
        if (isset($this->matchedProductIds[$product->id])) {
            return $this->matchedProductIds[$product->id];
        }

        if (! $this->enabled()) {
            return [];
        }

        $product->loadMissing(['template.category', 'category']);

        $collections = Collection::query()
            ->whereNull('shop_id')
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'name', 'description', 'keywords']);

        if ($collections->isEmpty()) {
            return [];
        }

        $catalog = $collections->map(fn (Collection $collection) => [
            'id' => $collection->id,
            'name' => $collection->name,
            'description' => $this->plainText($collection->description, 240),
            'keywords' => implode(', ', $this->keywordSync->normalize($collection->keywords)),
        ])->values()->all();

        $payload = [
            'product' => $this->productPayload($product),
            'collections' => $catalog,
        ];

        $max = max(1, (int) config('catalog.collection_ai.max_collections_per_product', 8));
        $text = $this->complete(
            'You assign print-on-demand products to merchandising collections.'.
            ' Pick collections the product clearly belongs in. Prefer theme, occasion, audience, and category fit.'.
            ' Do not force a match. Return JSON only: {"collection_ids":[1,2]} with at most '.$max.' ids from the list.',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $ids = $this->parseIds($text, $collections->pluck('id')->all());
        $ids = array_slice($ids, 0, $max);

        $this->refreshAiMemberships($product, $ids);

        Log::info('AI matched product to collections', [
            'product_id' => $product->id,
            'collection_ids' => $ids,
        ]);

        $this->matchedProductIds[$product->id] = $ids;

        return $ids;
    }

    /**
     * @return array<int, int>
     */
    public function matchCollection(Collection $collection): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $matched = [];
        $chunkSize = max(5, (int) config('catalog.collection_ai.product_chunk', 25));

        Product::query()
            ->with(['template.category', 'category'])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($products) use ($collection, &$matched) {
                $batch = $products->map(fn (Product $product) => $this->productPayload($product))->values()->all();
                if ($batch === []) {
                    return;
                }

                $text = $this->complete(
                    'You pick which catalog products belong in one merchandising collection.'.
                    ' Only include a clear thematic fit. Return JSON only: {"product_ids":[1,2]}.',
                    json_encode([
                        'collection' => [
                            'id' => $collection->id,
                            'name' => $collection->name,
                            'description' => $this->plainText($collection->description, 400),
                            'keywords' => implode(', ', $this->keywordSync->normalize($collection->keywords)),
                        ],
                        'products' => $batch,
                    ], JSON_UNESCAPED_UNICODE)
                );

                $ids = $this->parseIds($text, $products->pluck('id')->all(), 'product_ids');
                foreach ($ids as $id) {
                    $matched[$id] = $id;
                }
            });

        $matchedIds = array_values($matched);
        $this->replaceCollectionAiProducts($collection, $matchedIds);

        Log::info('AI matched collection products', [
            'collection_id' => $collection->id,
            'products_count' => count($matchedIds),
        ]);

        return $matchedIds;
    }

    /**
     * @param  array<int, int>  $collectionIds
     */
    public function refreshAiMemberships(Product $product, array $collectionIds): void
    {
        $this->keywordSync->attachMissing(
            $product,
            array_values(array_unique(array_map('intval', $collectionIds))),
            'ai'
        );
    }

    /**
     * @param  array<int, int>  $productIds
     */
    public function replaceCollectionAiProducts(Collection $collection, array $productIds): void
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));

        $currentAi = $collection->products()
            ->wherePivot('source', 'ai')
            ->pluck('products.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $remove = array_values(array_diff($currentAi, $productIds));
        if ($remove !== []) {
            $collection->products()->detach($remove);
        }

        $already = $collection->products()->pluck('products.id')->map(fn ($id) => (int) $id)->all();
        $add = array_values(array_diff($productIds, $already));
        if ($add === []) {
            return;
        }

        $payload = [];
        foreach ($add as $id) {
            $payload[$id] = ['source' => 'ai'];
        }
        $collection->products()->syncWithoutDetaching($payload);
    }

    /**
     * @return array<string, mixed>
     */
    protected function productPayload(Product $product): array
    {
        $keywords = $this->keywordSync->normalize($product->keywords ?? []);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $this->plainText($product->description ?? $product->template?->description, 320),
            'category' => $product->category?->name ?? $product->template?->category?->name,
            'template' => $product->template?->name,
            'keywords' => implode(', ', $keywords),
        ];
    }

    protected function plainText(?string $html, int $limit = 300): string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html))) ?? '');

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit);
    }

    /**
     * @param  array<int, int|string>  $allowed
     * @return array<int, int>
     */
    protected function parseIds(string $text, array $allowed, string $key = 'collection_ids'): array
    {
        $allowed = array_map('intval', $allowed);
        $json = $this->extractJson($text);
        $raw = $json[$key] ?? $json['ids'] ?? [];
        if (! is_array($raw)) {
            $raw = [];
        }

        $ids = [];
        foreach ($raw as $value) {
            $id = (int) $value;
            if ($id > 0 && in_array($id, $allowed, true)) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractJson(string $text): array
    {
        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = trim(preg_replace('/^```[a-zA-Z]*\n?|\n?```$/', '', $text) ?? $text);
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    protected function complete(string $system, string $user): string
    {
        $url = rtrim($this->baseUrl(), '/').'/chat/completions';
        $key = trim((string) config('studio.ai.completion_api_key') ?: (string) config('studio.ai.api_key'));

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(90)
            ->connectTimeout(20)
            ->post($url, [
                'model' => $this->chatModel(),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'temperature' => 0.1,
                'max_tokens' => 400,
                'stream' => false,
            ]);

        if (! $response->successful()) {
            Log::warning('Collection AI chat failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return '';
        }

        $json = $response->json();
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

            return trim(implode("\n", $parts));
        }

        return trim((string) ($content ?: data_get($json, 'choices.0.text') ?: ''));
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

    protected function baseUrl(): string
    {
        $url = trim((string) config('studio.ai.completion_base_url'));
        if ($url === '') {
            $url = trim((string) config('studio.ai.base_url', 'https://api.openai.com/v1'));
        }

        return $url !== '' ? rtrim($url, '/') : 'https://api.openai.com/v1';
    }
}
