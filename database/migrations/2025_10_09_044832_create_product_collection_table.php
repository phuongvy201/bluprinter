<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_collection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0); // For custom ordering within collection
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['product_id', 'collection_id']);

            // Indexes for performance
            $table->index(['collection_id', 'sort_order']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_collection');
    }
};
