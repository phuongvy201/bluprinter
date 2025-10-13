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
        Schema::create('template_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('variant_name'); // "Army Green, S"
            $table->string('size')->nullable(); // "S", "M", "L"
            $table->string('color')->nullable(); // "Army Green", "Black"
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->nullable(); // Override từ template base_price
            $table->integer('quantity')->default(0);
            $table->json('media')->nullable(); // Media riêng cho variant
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('product_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_variants');
    }
};
