<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_token', 64)->nullable()->index();
            $table->text('prompt');
            $table->json('image_urls');
            $table->json('reference_urls')->nullable();
            $table->string('image_model', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('hidden_from_customer')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_ai_generations');
    }
};
