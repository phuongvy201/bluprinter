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
            $table->string('created_by')->nullable()->after('status');
            $table->unsignedBigInteger('api_token_id')->nullable()->after('created_by');

            $table->foreign('api_token_id')->references('id')->on('api_tokens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['api_token_id']);
            $table->dropColumn(['created_by', 'api_token_id']);
        });
    }
};
