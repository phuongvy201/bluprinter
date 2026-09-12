<?php

namespace App\Jobs;

use App\Models\Collection;
use App\Services\CollectionAiMatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatchCollectionProductsWithAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $collectionId) {}

    public function handle(CollectionAiMatchService $matcher): void
    {
        if (! $matcher->enabled()) {
            return;
        }

        $collection = Collection::find($this->collectionId);
        if (! $collection) {
            return;
        }

        try {
            $matcher->matchCollection($collection);
        } catch (\Throwable $e) {
            Log::warning('AI collection match failed for collection', [
                'collection_id' => $this->collectionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
