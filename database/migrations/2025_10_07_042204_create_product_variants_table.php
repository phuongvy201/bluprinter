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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('template_id');
            $table->string('variant_name'); // "Size M - Black"
            $table->string('size')->nullable(); // "M", "L", "XL"
            $table->string('color')->nullable(); // "Black", "White"
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->nullable(); // Override từ product/template
            $table->integer('quantity')->default(0);
            $table->json('media')->nullable(); // Media riêng cho variant
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('product_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
