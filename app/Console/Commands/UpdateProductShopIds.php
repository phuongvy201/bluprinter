<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Shop;

class UpdateProductShopIds extends Command
{
    protected $signature = 'products:update-shop-ids';
    protected $description = 'Update shop_id for existing products based on their owner';

    public function handle()
    {
        $this->info('Updating shop_id for existing products...');

        $products = Product::whereNull('shop_id')->get();
        $updated = 0;

        foreach ($products as $product) {
            if ($product->user && $product->user->hasShop()) {
                $product->shop_id = $product->user->shop->id;
                $product->save();
                $updated++;

                $this->line("Updated product '{$product->name}' (ID: {$product->id}) with shop_id: {$product->shop_id}");
            }
        }

        $this->info("Updated {$updated} products with shop_id");

        // Also update shop total_products count
        $shops = Shop::all();
        foreach ($shops as $shop) {
            $actualCount = $shop->products()->count();
            if ($shop->total_products != $actualCount) {
                $shop->total_products = $actualCount;
                $shop->save();
                $this->line("Updated shop '{$shop->shop_name}' total_products to {$actualCount}");
            }
        }

        $this->info('Shop product counts updated successfully!');
    }
}
