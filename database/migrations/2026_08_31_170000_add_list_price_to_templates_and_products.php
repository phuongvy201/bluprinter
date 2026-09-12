<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            $table->decimal('list_price', 10, 2)->nullable()->after('base_price');
        });

        Schema::table('template_variants', function (Blueprint $table) {
            $table->decimal('list_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('list_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('list_price', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('product_templates', function (Blueprint $table) {
            $table->dropColumn('list_price');
        });

        Schema::table('template_variants', function (Blueprint $table) {
            $table->dropColumn('list_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('list_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('list_price');
        });
    }
};
