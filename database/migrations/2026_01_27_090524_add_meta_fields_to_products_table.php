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
        Schema::table('products', function (Blueprint $table) {
            $table->string('google_product_category', 200)->nullable()->after('status');
            $table->string('fb_product_category', 200)->nullable()->after('google_product_category');
            $table->string('gender', 20)->nullable()->after('fb_product_category'); // female, male, unisex
            $table->string('color', 200)->nullable()->after('gender');
            $table->string('age_group', 50)->nullable()->after('color'); // newborn, infant, toddler, kids, teen, adult, all ages
            $table->string('material', 200)->nullable()->after('age_group');
            $table->string('pattern', 100)->nullable()->after('material');
            $table->string('shipping', 200)->nullable()->after('pattern'); // Format: Country:Region:Service:Price
            $table->string('shipping_weight', 50)->nullable()->after('shipping'); // e.g., 200g
            $table->integer('quantity_to_sell_on_facebook')->default(100)->after('shipping_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'google_product_category',
                'fb_product_category',
                'gender',
                'color',
                'age_group',
                'material',
                'pattern',
                'shipping',
                'shipping_weight',
                'quantity_to_sell_on_facebook'
            ]);
        });
    }
};
