<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedVirtualProducts extends Command
{
    protected $signature = 'products:seed-virtual
                            {count=500 : Number of products to create}
                            {--template=1 : Product template ID}';

    protected $description = 'Create virtual demo products from a product template';

    public function handle(): int
    {
        $count = max(1, (int) $this->argument('count'));
        $templateId = (int) $this->option('template');

        $template = ProductTemplate::find($templateId);
        if (! $template) {
            $this->error("Template #{$templateId} not found.");

            return self::FAILURE;
        }

        $shop = Shop::where('shop_status', 'active')->first() ?? Shop::first();
        if (! $shop) {
            $this->error('No shop found. Create a shop first.');

            return self::FAILURE;
        }

        $basePrice = (float) ($template->base_price ?? 18.85);
        $description = $template->description ?? 'Demo product generated for storefront testing.';
        $media = $template->media;
        $prefix = Str::slug(Str::limit($template->name, 24, '')) ?: 'virtual-product';
        $existingSlugs = Product::where('slug', 'like', $prefix . '-%')->pluck('slug')->flip();

        $this->info("Creating {$count} virtual products from template #{$templateId} ({$template->name})…");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $created = 0;
        $now = now();

        Product::withoutEvents(function () use (
            $count,
            $template,
            $shop,
            $basePrice,
            $description,
            $media,
            $prefix,
            &$existingSlugs,
            $bar,
            $now,
            &$created,
        ) {
            for ($i = 1; $i <= $count; $i++) {
                $slug = $this->uniqueSlug("{$prefix}-{$i}", $existingSlugs);
                $existingSlugs[$slug] = true;

                $salePrice = round($basePrice * (mt_rand(70, 100) / 100), 2);

                Product::create([
                    'template_id' => $template->id,
                    'user_id' => $template->user_id ?? $shop->user_id,
                    'shop_id' => $shop->id,
                    'name' => "Virtual {$template->name} #{$i}",
                    'slug' => $slug,
                    'sku' => 'VIRT-' . strtoupper(Str::random(10)),
                    'price' => $salePrice,
                    'description' => $description,
                    'media' => $media,
                    'quantity' => mt_rand(50, 200),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $created++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Created {$created} products (shop: {$shop->name}).");

        return self::SUCCESS;
    }

    private function uniqueSlug(string $base, &$taken): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = 'virtual-product';
        }

        $candidate = $slug;
        $counter = 2;

        while (isset($taken[$candidate]) || Product::where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
