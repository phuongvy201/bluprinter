<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class CollectionKeywordSyncService
{
    /**
     * Normalize a free-text / array of keywords into unique lowercase tokens.
     *
     * @param  string|array|null  $input
     * @return array<int, string>
     */
    public function normalize(string|array|null $input): array
    {
        if ($input === null || $input === '') {
            return [];
        }

        if (is_array($input)) {
            $parts = $input;
        } else {
            $parts = preg_split('/[,;\n]+/', $input) ?: [];
        }

        $normalized = [];
        foreach ($parts as $part) {
            $token = mb_strtolower(trim((string) $part));
            if ($token === '') {
                continue;
            }
            $normalized[$token] = $token;
        }

        return array_values($normalized);
    }

    /**
     * Sync one product into all keyword collections it matches.
     * Detaches keyword-sourced rows that no longer match. Leaves AI/manual rows.
     */
    public function syncProduct(Product $product): void
    {
        $productKeywords = $this->normalize($product->keywords ?? []);

        $keywordCollections = Collection::query()
            ->whereNotNull('keywords')
            ->get()
            ->filter(fn (Collection $collection) => ! empty($this->normalize($collection->keywords)));

        $matchIds = [];

        foreach ($keywordCollections as $collection) {
            $collectionKeywords = $this->normalize($collection->keywords);
            if ($this->intersects($productKeywords, $collectionKeywords)) {
                $matchIds[] = (int) $collection->id;
            }
        }

        $keywordCollectionIds = $keywordCollections->pluck('id')->map(fn ($id) => (int) $id)->all();
        $stale = array_values(array_diff($keywordCollectionIds, $matchIds));

        if ($stale !== []) {
            $detachIds = $product->collections()
                ->whereIn('collections.id', $stale)
                ->wherePivot('source', 'keyword')
                ->pluck('collections.id')
                ->all();

            if ($detachIds !== []) {
                $product->collections()->detach($detachIds);
            }
        }

        $this->attachMissing($product, $matchIds, 'keyword');

        Log::info('Product synced to keyword collections', [
            'product_id' => $product->id,
            'product_keywords' => $productKeywords,
            'matched_collection_ids' => $matchIds,
        ]);
    }

    /**
     * Rebuild keyword membership for one collection without wiping AI/manual products.
     */
    public function syncCollection(Collection $collection): void
    {
        $collectionKeywords = $this->normalize($collection->keywords ?? []);

        if (empty($collectionKeywords)) {
            Log::info('Collection has no keywords, skip auto sync', [
                'collection_id' => $collection->id,
            ]);

            return;
        }

        $attachIds = [];

        Product::query()
            ->whereNotNull('keywords')
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($collectionKeywords, &$attachIds) {
                foreach ($products as $product) {
                    $productKeywords = $this->normalize($product->keywords ?? []);
                    if ($this->intersects($productKeywords, $collectionKeywords)) {
                        $attachIds[] = (int) $product->id;
                    }
                }
            });

        $stale = $collection->products()
            ->wherePivot('source', 'keyword')
            ->pluck('products.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $remove = array_values(array_diff($stale, $attachIds));
        if ($remove !== []) {
            $collection->products()->detach($remove);
        }

        $already = $collection->products()->pluck('products.id')->map(fn ($id) => (int) $id)->all();
        $add = array_values(array_diff($attachIds, $already));
        if ($add !== []) {
            $payload = [];
            foreach ($add as $id) {
                $payload[$id] = ['source' => 'keyword'];
            }
            $collection->products()->syncWithoutDetaching($payload);
        }

        Log::info('Collection rebuilt from product keywords', [
            'collection_id' => $collection->id,
            'collection_keywords' => $collectionKeywords,
            'products_count' => count($attachIds),
        ]);
    }

    /**
     * @param  array<int, int>  $collectionIds
     */
    public function attachMissing(Product $product, array $collectionIds, string $source): void
    {
        $already = $product->collections()->pluck('collections.id')->map(fn ($id) => (int) $id)->all();
        $add = array_values(array_diff($collectionIds, $already));
        if ($add === []) {
            return;
        }

        $payload = [];
        foreach ($add as $id) {
            $payload[$id] = ['source' => $source];
        }
        $product->collections()->syncWithoutDetaching($payload);
    }

    /**
     * @param  array<int, string>  $a
     * @param  array<int, string>  $b
     */
    protected function intersects(array $a, array $b): bool
    {
        if (empty($a) || empty($b)) {
            return false;
        }

        return count(array_intersect($a, $b)) > 0;
    }
}
