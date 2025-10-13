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
        Schema::table('product_variants', function (Blueprint $table) {
            // Remove fixed size and color columns
            $table->dropColumn(['size', 'color']);

            // Add dynamic attributes column
            $table->json('attributes')->nullable()->after('variant_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Add back the fixed columns
            $table->string('size')->nullable()->after('variant_name');
            $table->string('color')->nullable()->after('size');

            // Remove dynamic attributes column
            $table->dropColumn('attributes');
        });
    }
};
