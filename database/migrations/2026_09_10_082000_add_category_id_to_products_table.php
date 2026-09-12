<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('template_id');
                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('products', 'category_id')) {
            DB::statement('
                UPDATE products
                INNER JOIN product_templates ON products.template_id = product_templates.id
                SET products.category_id = product_templates.category_id
                WHERE products.category_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
};
