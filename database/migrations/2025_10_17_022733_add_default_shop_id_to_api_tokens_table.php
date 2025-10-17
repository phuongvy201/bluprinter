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
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->foreignId('default_shop_id')
                ->nullable()
                ->after('permissions')
                ->constrained('shops')
                ->onDelete('set null')
                ->comment('Default shop ID for products created via this API token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropForeign(['default_shop_id']);
            $table->dropColumn('default_shop_id');
        });
    }
};
