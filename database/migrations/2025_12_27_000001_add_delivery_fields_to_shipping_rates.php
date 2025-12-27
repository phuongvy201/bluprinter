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
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->unsignedSmallInteger('delivery_min_days')->nullable()->after('description');
            $table->unsignedSmallInteger('delivery_max_days')->nullable()->after('delivery_min_days');
            $table->string('delivery_note')->nullable()->after('delivery_max_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->dropColumn(['delivery_min_days', 'delivery_max_days', 'delivery_note']);
        });
    }
};
