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
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('total_price');
            $table->boolean('is_first_item')->default(false)->after('shipping_cost');
            $table->text('shipping_notes')->nullable()->after('is_first_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'is_first_item', 'shipping_notes']);
        });
    }
};
