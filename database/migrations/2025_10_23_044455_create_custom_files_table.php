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
        Schema::create('custom_files', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // For guest users
            $table->unsignedBigInteger('user_id')->nullable(); // For authenticated users
            $table->unsignedBigInteger('product_id');
            $table->string('original_name'); // Original filename
            $table->string('filename'); // Generated filename
            $table->string('file_path'); // S3 path
            $table->string('file_url'); // Full S3 URL
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // Size in bytes
            $table->string('file_extension');
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('expires_at')->nullable(); // For temporary files
            $table->timestamps();

            // Indexes
            $table->index(['session_id', 'product_id']);
            $table->index(['user_id', 'product_id']);
            $table->index('status');
            $table->index('expires_at');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_files');
    }
};
