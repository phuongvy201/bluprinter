<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSkuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generate SKU for all products that don't have one yet.
     */
    public function run(): void
    {
        // Get all products without SKU
        $products = Product::whereNull('sku')
            ->orWhere('sku', '')
            ->get();

        if ($products->isEmpty()) {
            $this->command->info('✅ All products already have SKU.');
            return;
        }

        $this->command->info("📦 Found {$products->count()} products without SKU. Generating SKUs...");

        $bar = $this->command->getOutput()->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $sku = $this->generateUniqueSKU();
            $product->update(['sku' => $sku]);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Successfully generated SKU for {$products->count()} products.");
    }

    /**
     * Generate a unique SKU for a product
     * Format: PRD-{random 8 characters}
     * 
     * @return string Unique SKU
     */
    private function generateUniqueSKU(): string
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }
}
