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
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Nested Categories
            $table->foreignId('parent_id')->nullable()->constrained('post_categories')->onDelete('cascade');

            // Display
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable(); // Hex color for UI
            $table->string('icon')->nullable(); // Icon class or emoji

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Stats
            $table->integer('posts_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('slug');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_categories');
    }
};
