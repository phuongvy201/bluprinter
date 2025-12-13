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
        Schema::create('domain_shipping_costs', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->index();
            $table->string('region', 10)->index(); // US, UK, CA, MX, etc.
            $table->string('product_type', 50); // clothing, ornaments
            $table->decimal('first_item_cost', 10, 2); // Giá USD
            $table->decimal('additional_item_cost', 10, 2); // Giá USD
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['domain', 'region', 'product_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_shipping_costs');
    }
};
