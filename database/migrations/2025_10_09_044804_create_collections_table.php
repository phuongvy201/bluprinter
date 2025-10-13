<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator/Owner
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Collection cover image
            $table->enum('type', ['manual', 'automatic'])->default('manual'); // Manual or auto-generated
            $table->json('auto_rules')->nullable(); // Rules for automatic collections
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->integer('sort_order')->default(0); // For ordering collections
            $table->boolean('featured')->default(false); // Featured collection
            $table->string('meta_title')->nullable(); // SEO
            $table->text('meta_description')->nullable(); // SEO
            $table->timestamps();

            // Indexes
            $table->index(['status', 'featured']);
            $table->index(['user_id', 'status']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
