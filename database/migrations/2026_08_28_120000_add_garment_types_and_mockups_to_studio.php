<?php

use App\Models\Product;
use App\Models\StudioProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_products', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('slug')->nullable()->after('name');
            $table->string('category')->nullable()->after('slug');
            $table->decimal('price', 10, 2)->default(0)->after('category');
            $table->json('colors')->nullable()->after('price');
            $table->json('sizes')->nullable()->after('colors');
        });

        Schema::table('studio_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        try {
            Schema::table('studio_products', function (Blueprint $table) {
                $table->dropUnique(['product_id']);
            });
        } catch (\Throwable) {
            // Index name can differ between drivers.
        }

        DB::statement('ALTER TABLE studio_products MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('studio_products', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index('slug');
            $table->index('category');
        });

        Schema::create('studio_mockups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_product_id')->constrained('studio_products')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('image_url');
            $table->string('color')->nullable();
            $table->json('print_area')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_mockups');

        Schema::table('studio_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropIndex(['slug']);
            $table->dropIndex(['category']);
            $table->dropColumn(['name', 'slug', 'category', 'price', 'colors', 'sizes']);
        });

        DB::statement('ALTER TABLE studio_products MODIFY product_id BIGINT UNSIGNED NOT NULL');

        Schema::table('studio_products', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique('product_id');
        });
    }

    protected function backfill(): void
    {
        $rows = DB::table('studio_products')->get();
        foreach ($rows as $row) {
            $product = $row->product_id ? Product::query()->find($row->product_id) : null;
            $name = $product?->name ?: 'Garment';
            DB::table('studio_products')->where('id', $row->id)->update([
                'name' => $name,
                'slug' => Str::slug($name) ?: 'garment-'.$row->id,
                'category' => 'Clothing',
                'price' => $product?->price ?? 0,
            ]);

            $image = $row->mockup_url;
            if (!$image && $product) {
                $media = $product->getEffectiveMedia();
                $first = $media[0] ?? null;
                $image = is_string($first) ? $first : ($first['url'] ?? $first['path'] ?? null);
            }
            if ($image) {
                DB::table('studio_mockups')->insert([
                    'studio_product_id' => $row->id,
                    'name' => 'Front',
                    'image_url' => $image,
                    'print_area' => $row->print_area,
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
