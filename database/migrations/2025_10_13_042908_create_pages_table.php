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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Admin who created

            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content'); // HTML content
            $table->text('excerpt')->nullable(); // Short description

            // Featured Image
            $table->string('featured_image')->nullable();

            // Status & Visibility
            $table->enum('status', ['published', 'draft', 'scheduled'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Template & Position
            $table->string('template')->default('default'); // default, fullwidth, sidebar, etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('show_in_menu')->default(false); // Show in navigation menu
            $table->string('menu_title')->nullable(); // Custom title for menu

            // Parent page for nested structure
            $table->foreignId('parent_id')->nullable()->constrained('pages')->onDelete('cascade');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Analytics
            $table->integer('views')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('slug');
            $table->index(['status', 'published_at']);
            $table->index('show_in_menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
