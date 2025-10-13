<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class FillProductOwners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fill-owners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill user_id for existing products based on template owner';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Filling user_id for products without owner...');

        $products = Product::with('template')->whereNull('user_id')->get();

        if ($products->count() === 0) {
            $this->info('No products need updating.');
            return Command::SUCCESS;
        }

        $updated = 0;

        foreach ($products as $product) {
            if ($product->template && $product->template->user_id) {
                $product->user_id = $product->template->user_id;
                $product->save();
                $updated++;
            }
        }

        $this->info("Updated {$updated} products with owner from template.");

        return Command::SUCCESS;
    }
}
