<?php

namespace App\Console\Commands;

use App\Jobs\MatchCollectionProductsWithAi;
use App\Jobs\MatchProductToCollectionsWithAi;
use App\Models\Collection;
use App\Models\Product;
use App\Services\CollectionAiMatchService;
use Illuminate\Console\Command;

class MatchCollectionsWithAi extends Command
{
    protected $signature = 'collections:ai-match
                            {--product= : Match one product ID}
                            {--collection= : Match one collection ID}
                            {--sync : Run immediately instead of queueing}';

    protected $description = 'Use Studio AI to attach products to merchandising collections';

    public function handle(CollectionAiMatchService $matcher): int
    {
        if (! $matcher->enabled()) {
            $this->error('Collection AI is disabled or STUDIO_AI_API_KEY / chat model is missing.');

            return self::FAILURE;
        }

        $productId = $this->option('product');
        $collectionId = $this->option('collection');
        $sync = (bool) $this->option('sync');

        if ($productId) {
            $this->runProduct((int) $productId, $sync, $matcher);

            return self::SUCCESS;
        }

        if ($collectionId) {
            $this->runCollection((int) $collectionId, $sync, $matcher);

            return self::SUCCESS;
        }

        $count = 0;
        Collection::query()->whereNull('shop_id')->where('status', 'active')->orderBy('id')
            ->each(function (Collection $collection) use ($sync, $matcher, &$count) {
                $this->runCollection($collection->id, $sync, $matcher);
                $count++;
            });

        $this->info("Queued/ran AI matching for {$count} collections.");

        return self::SUCCESS;
    }

    protected function runProduct(int $id, bool $sync, CollectionAiMatchService $matcher): void
    {
        if (! Product::find($id)) {
            $this->error("Product {$id} not found.");

            return;
        }

        if ($sync) {
            $ids = $matcher->matchProduct(Product::findOrFail($id));
            $this->info('Product '.$id.' → collections: '.(implode(', ', $ids) ?: 'none'));

            return;
        }

        MatchProductToCollectionsWithAi::dispatch($id);
        $this->info("Queued product {$id}.");
    }

    protected function runCollection(int $id, bool $sync, CollectionAiMatchService $matcher): void
    {
        if (! Collection::find($id)) {
            $this->error("Collection {$id} not found.");

            return;
        }

        if ($sync) {
            $ids = $matcher->matchCollection(Collection::findOrFail($id));
            $this->info('Collection '.$id.' → products: '.count($ids));

            return;
        }

        MatchCollectionProductsWithAi::dispatch($id);
        $this->info("Queued collection {$id}.");
    }
}
