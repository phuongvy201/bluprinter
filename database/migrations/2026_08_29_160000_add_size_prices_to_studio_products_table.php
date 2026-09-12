<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_products', function (Blueprint $table) {
            $table->json('size_prices')->nullable()->after('sizes');
        });
    }

    public function down(): void
    {
        Schema::table('studio_products', function (Blueprint $table) {
            $table->dropColumn('size_prices');
        });
    }
};
