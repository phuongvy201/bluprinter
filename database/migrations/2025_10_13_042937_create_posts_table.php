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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Seller who created
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('cascade'); // Shop owner
            $table->foreignId('post_category_id')->nullable()->constrained('post_categories')->onDelete('set null');

            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content'); // HTML content
            $table->text('excerpt')->nullable(); // Short description

            // Featured Image
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // Additional images

            // Status & Visibility
            $table->enum('status', ['published', 'draft', 'scheduled', 'pending'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Post Type
            $table->enum('type', ['article', 'video', 'gallery', 'product_review'])->default('article');

            // Featured & Sticky
            $table->boolean('featured')->default(false);
            $table->boolean('sticky')->default(false); // Pin to top

            // Comments
            $table->boolean('allow_comments')->default(true);
            $table->integer('comments_count')->default(0);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Analytics
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('shares')->default(0);

            // Reading Time (auto calculated)
            $table->integer('reading_time')->default(0); // in minutes

            $table->timestamps();

            // Indexes
            $table->index('slug');
            $table->index(['status', 'published_at']);
            $table->index(['shop_id', 'status']);
            $table->index('post_category_id');
            $table->index(['featured', 'sticky']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
