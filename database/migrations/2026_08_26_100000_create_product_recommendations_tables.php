<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->string('source', 32)->default('co_occurrence');
            $table->unsignedSmallInteger('rank')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id']);
            $table->index(['product_id', 'source', 'rank']);
        });

        Schema::create('product_co_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id']);
            $table->index(['product_id', 'view_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_co_views');
        Schema::dropIfExists('product_recommendations');
    }
};
