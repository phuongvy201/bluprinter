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
        Schema::table('gmc_configs', function (Blueprint $table) {
            $table->decimal('currency_rate', 10, 6)->nullable()->after('currency')->comment('Tỉ giá chuyển đổi từ USD (ví dụ: 0.79 cho GBP, 25000 cho VND)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gmc_configs', function (Blueprint $table) {
            $table->dropColumn('currency_rate');
        });
    }
};
