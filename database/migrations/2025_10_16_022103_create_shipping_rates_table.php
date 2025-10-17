<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('name'); // e.g., "Standard Shipping - T-Shirts"
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('first_item_cost', 10, 2); // Base shipping cost for first item (includes all fees)
            $table->decimal('additional_item_cost', 10, 2)->default(0); // Cost for each additional item

            // Optional conditions
            $table->integer('min_items')->nullable(); // Minimum items for this rate
            $table->integer('max_items')->nullable(); // Maximum items for this rate
            $table->decimal('min_order_value', 10, 2)->nullable(); // Min order value required
            $table->decimal('max_order_value', 10, 2)->nullable(); // Max order value limit

            // Weight-based (optional)
            $table->decimal('max_weight', 10, 2)->nullable(); // Maximum weight in kg

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['shipping_zone_id', 'category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
