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
        Schema::create('domain_analytics_configs', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique()->comment('Domain name (e.g., example.com)');
            $table->string('property_id')->comment('Google Analytics Property ID (e.g., 123456789)');
            $table->string('credentials_path')->comment('Path to Google Analytics credentials JSON file');
            $table->boolean('is_active')->default(true)->comment('Whether this config is active');
            $table->text('notes')->nullable()->comment('Optional notes about this configuration');
            $table->timestamps();

            $table->index('domain');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_analytics_configs');
    }
};
