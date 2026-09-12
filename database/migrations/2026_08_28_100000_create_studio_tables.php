<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('mockup_url')->nullable();
            $table->json('print_area')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('product_id');
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('studio_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_url');
            $table->string('tag')->nullable();
            $table->decimal('price', 10, 2)->default(3.00);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_designs');
        Schema::dropIfExists('studio_products');
    }
};
