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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Nullable for guest users
            $table->string('session_id')->nullable(); // For guest users
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'product_id']);
            $table->index(['session_id', 'product_id']);
            $table->index('product_id');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Unique constraints
            $table->unique(['user_id', 'product_id'], 'unique_user_product');
            $table->unique(['session_id', 'product_id'], 'unique_session_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
